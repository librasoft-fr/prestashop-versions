<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Controller;

use NeuronAI\Chat\Messages\Stream\Chunks\ReasoningChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\StreamChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\ToolCallChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\ToolResultChunk;
use NeuronAI\Workflow\Interrupt\Action;
use PrestaShop\Module\PsAskAi\Config\PsAskAiConfig;
use PrestaShop\Module\PsAskAi\Entity\PsAskAiConversation;
use PrestaShop\Module\PsAskAi\Exception\ApiKeyValidationException;
use PrestaShop\Module\PsAskAi\Service\Chat\SerializableSqlChatHistory;
use PrestaShop\Module\PsAskAi\Service\ConversationService;
use PrestaShop\Module\PsAskAi\Service\Mcp\McpToolName;
use PrestaShop\Module\PsAskAi\Service\Mcp\ReasonedMcpConnector;
use PrestaShop\Module\PsAskAi\Service\McpServerService;
use PrestaShop\Module\PsAskAi\Service\ModuleCatalogService;
use PrestaShop\Module\PsAskAi\Service\ModuleInstallerService;
use PrestaShop\Module\PsAskAi\Service\NeuronAiService;
use PrestaShop\Module\PsAskAi\Service\PrepromptService;
use PrestaShop\Module\PsAskAi\Service\SegmentService;
use PrestaShop\Module\PsAskAi\Service\SentryReporter;
use PrestaShop\Module\PsAskAi\Service\SuggestionsService;
use PrestaShop\Module\PsAskAi\Tools\ImageGenerationTool;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Back-office endpoints powering the production Ask AI feature. The frontend
 * is wired separately; here we only expose the request/stream surface.
 *
 * Each endpoint accepts an optional `model` / `api_key` in the request; when
 * either is missing, we fall back to the value stored in PrestaShop's
 * Configuration (set via the module's configure page). When the API key is
 * still empty after both lookups, NeuronAiService falls back to the matching
 * PS_ASK_AI__*_API_KEY env var.
 */
class AskAiController extends FrameworkBundleAdminController
{
    /**
     * Tool results can be arbitrarily large (product lists, HTML pages) —
     * cap what goes over SSE / into history JSON; the full result stays in
     * the chat history rows for the LLM.
     */
    private const RESULT_PREVIEW_MAX_BYTES = 4096;

    public function __construct(
        private readonly NeuronAiService $neuronAiService,
        private readonly ConversationService $conversationService,
        private readonly McpServerService $mcpServerService,
        private readonly PrepromptService $prepromptService,
        private readonly SuggestionsService $suggestionsService,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?SegmentService $segmentService = null,
        private readonly ?ModuleInstallerService $moduleInstallerService = null,
        private readonly ?ModuleCatalogService $moduleCatalogService = null,
        private readonly ?SentryReporter $sentryReporter = null,
    ) {
    }

    public function streamAction(Request $request): StreamedResponse
    {
        $this->warmUpSessionDependentServices();

        $prompt = (string) $request->query->get('prompt', '');
        $requestedThreadId = $this->nullIfEmpty((string) $request->query->get('thread', ''));
        // Fork (PE-1721): when fork_from is set, the merchant edited an earlier
        // message and resent it — spawn a child thread holding a copy of the
        // source's messages before the edited one (fork_user_ordinal), then run
        // the prompt there. The source thread is left intact.
        $forkFrom = $this->nullIfEmpty((string) $request->query->get('fork_from', ''));
        $forkUserOrdinal = max(0, $request->query->getInt('fork_user_ordinal'));
        $urlContext = (bool) $request->query->getInt('url_context');
        $provider = (string) \Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
        [$modelKey, $apiKey] = $this->resolveModelAndKey(
            $this->nullIfEmpty((string) $request->query->get('model', '')),
            $this->nullIfEmpty((string) $request->query->get('api_key', '')),
        );
        // query->all($key) requires Symfony >= 5.1; on PS 8 (SF 4.4) the
        // argument is ignored and ALL query params come back. Index instead.
        $imageUrlsRaw = $request->query->all()['image_urls'] ?? [];
        $imageUrls = array_values(array_filter(array_map('strval', is_array($imageUrlsRaw) ? $imageUrlsRaw : [])));

        // Resolve and touch the conversation BEFORE the streamed response
        // begins emitting. Doctrine flush can lazily bootstrap PrestaShop's
        // session; once StreamedResponse has sent SSE headers, any subsequent
        // session_start() fails with "headers already sent". Doing it here
        // also means updated_at reflects when the user started the turn — a
        // turn that crashes mid-stream still surfaces in the recents list.
        $conversation = null;
        if ($prompt !== '') {
            $conversation = $forkFrom !== null
                ? $this->resolveForkConversation($forkFrom, $forkUserOrdinal)
                : $this->resolveConversationForCurrentEmployee($requestedThreadId);
        }
        if ($conversation !== null) {
            $this->conversationService->touch($conversation);
            // Keep the tree root fresh too, so recents ordering (which lists
            // roots only) reflects activity happening on a fork branch.
            $this->touchRoot($conversation);
        }

        return $this->buildStreamResponse(function (callable $emit) use ($prompt, $provider, $conversation, $urlContext, $modelKey, $apiKey, $imageUrls): void {
            if ($prompt === '') {
                $emit('error-event', ['message' => 'Empty prompt']);

                return;
            }
            if ($conversation === null) {
                $emit('error-event', ['message' => 'Conversation not found or not accessible.']);

                return;
            }
            $emit('thread', ['threadId' => $conversation->getThreadId()]);

            $this->neuronAiService->stream(
                $prompt,
                $provider,
                $conversation->getThreadId(),
                $this->prepromptService->get(),
                $this->buildChunkEmitter($emit),
                static function (array $meta) use ($emit): void {
                    $emit('meta', $meta);
                },
                $this->buildApprovalEmitter($emit, $conversation->getThreadId()),
                $urlContext,
                $modelKey,
                $apiKey,
                $imageUrls,
            );
        }, array_filter([
            'provider' => $this->neuronAiService->getModelProvider($modelKey),
            'model' => $modelKey,
            'route' => 'stream',
        ]), array_filter([
            'thread_id' => $conversation?->getThreadId(),
        ]));
    }

    public function uploadAction(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return new JsonResponse(['error' => 'No valid file uploaded'], 400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType() ?? '';
        if (!in_array($mimeType, $allowedMimes, true)) {
            return new JsonResponse(['error' => 'Only JPEG, PNG, GIF and WebP images are allowed'], 400);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return new JsonResponse(['error' => 'File exceeds 10 MB limit'], 400);
        }

        if (!defined('_PS_TMP_IMG_DIR_')) {
            return new JsonResponse(['error' => 'Image storage not available'], 500);
        }

        $dir = _PS_TMP_IMG_DIR_ . 'ps_ask_ai/';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return new JsonResponse(['error' => 'Could not create upload directory'], 500);
        }

        // Purge files older than 30d on each upload (best-effort, ignore errors)
        foreach ((array) glob($dir . '*') as $old) {
            if (is_file((string) $old) && time() - filemtime((string) $old) > 86400 * 30) {
                @unlink((string) $old);
            }
        }

        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            default => 'webp',
        };

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $file->move($dir, $filename);

        $shopBaseUrl = rtrim(\Context::getContext()->link->getBaseLink(), '/');
        $publicUrl = $shopBaseUrl . '/img/tmp/ps_ask_ai/' . $filename;

        return new JsonResponse(['url' => $publicUrl]);
    }

    public function historyAction(Request $request): JsonResponse
    {
        $threadId = $this->nullIfEmpty((string) $request->query->get('thread', ''));
        if ($threadId === null) {
            return new JsonResponse(['error' => 'Missing thread'], 400);
        }

        $conversation = $this->resolveConversationForCurrentEmployee($threadId);
        if ($conversation === null) {
            return new JsonResponse(['error' => 'Conversation not found or not accessible.'], 404);
        }

        // A root opened from recents resolves to the branch the merchant was
        // last on (its active leaf); a fork opened directly (e.g. cross-tab
        // mirror to a specific branch) is loaded as-is. The resolved threadId
        // is returned so the frontend adopts the branch it actually shows.
        // `exact=1` (variation navigation, PE-1777) suppresses that resolution:
        // the caller already knows which branch it wants and a root member of a
        // variation group must return its OWN transcript, not the active leaf.
        if (!$request->query->getBoolean('exact') && $conversation->isRoot()) {
            $conversation = $this->conversationService->getActiveLeaf($conversation);
        }

        $messages = $this->shapeHistory($this->neuronAiService->getHistory($conversation->getThreadId()));
        $messages = $this->attachVariations($messages, $this->conversationService->computeVariations($conversation));

        return new JsonResponse([
            'threadId' => $conversation->getThreadId(),
            'messages' => $messages,
            'pendingApproval' => $this->neuronAiService->loadPendingApproval($conversation->getThreadId()),
        ]);
    }

    public function modelsAction(): JsonResponse
    {
        $activeProvider = (string) \Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
        if ($activeProvider === '' || !in_array($activeProvider, PsAskAiConfig::SUPPORTED_PROVIDERS, true)) {
            return new JsonResponse([
                'models' => [],
                'providers' => [],
                'default' => null,
                'current' => null,
            ]);
        }

        // Pass the shop's configured key so the live /models probe works when
        // the key lives in BO Configuration (the normal case), not just env.
        // Null falls back to the env var inside NeuronAiService::resolveApiKey.
        $storedKey = $this->nullIfEmpty((string) \Configuration::get(PsAskAiConfig::CONFIG_API_KEY));
        $available = $this->neuronAiService->getAvailableModels($activeProvider, $storedKey);
        $models = [];
        foreach ($available as $key => $config) {
            $models[] = [
                'key' => $key,
                'label' => $config['label'],
                'provider' => $config['provider'],
            ];
        }

        // Default must be a model actually offered: the operator-flagged default
        // when it survived the reachability intersection, otherwise the first
        // offered model (null when nothing is reachable). The /models probe
        // behind this is file-cached, so it reuses the intersection above.
        $defaultKey = $this->neuronAiService->getDefaultModelKey($activeProvider, $storedKey);

        $stored = (string) \Configuration::get(PsAskAiConfig::CONFIG_MODEL_KEY);
        $current = $stored !== '' && isset($available[$stored]) ? $stored : $defaultKey;

        return new JsonResponse([
            'models' => $models,
            'providers' => [$activeProvider],
            'default' => $defaultKey,
            'current' => $current,
        ]);
    }

    /**
     * Return the active conversations owned by the current employee + shop,
     * most recently updated first. The frontend uses this to restore the last
     * thread on drawer open and to render a conversation list.
     */
    public function listAction(): JsonResponse
    {
        $employee = \Context::getContext()->employee ?? null;
        $shop = \Context::getContext()->shop ?? null;
        if ($employee === null || $shop === null) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $conversations = $this->conversationService->listForEmployee((int) $employee->id, (int) $shop->id);

        $payload = [];
        foreach ($conversations as $conversation) {
            $payload[] = [
                'threadId' => $conversation->getThreadId(),
                'title' => $conversation->getTitle(),
                'createdAt' => $conversation->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'updatedAt' => $conversation->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        return new JsonResponse($payload);
    }

    public function selectModelAction(Request $request): JsonResponse
    {
        $key = $this->nullIfEmpty((string) $request->request->get('key', ''))
            ?? $this->nullIfEmpty((string) $request->query->get('key', ''));

        if ($key === null) {
            return new JsonResponse(['error' => 'Missing key'], 400);
        }

        // Validate against the curated whitelist (no live /models call on a
        // simple persist). The provider is configured separately, so a key just
        // needs to be a known chat model; the frontend only offers models that
        // also passed the reachability intersection.
        if (!in_array($key, $this->neuronAiService->getModelKeys(), true)) {
            return new JsonResponse(['error' => 'Unknown model key'], 400);
        }

        \Configuration::updateValue(PsAskAiConfig::CONFIG_MODEL_KEY, $key);

        return new JsonResponse(['key' => $key]);
    }

    public function reportErrorAction(Request $request): JsonResponse
    {
        $type = $this->nullIfEmpty((string) $request->request->get('type', ''))
            ?? $this->nullIfEmpty((string) $request->query->get('type', ''));

        if ($type === 'invalidApiKey') {
            \Configuration::updateValue(PsAskAiConfig::CONFIG_API_KEY_ERROR, '1');
        } elseif ($type === 'providerQuota') {
            \Configuration::updateValue(PsAskAiConfig::CONFIG_PROVIDER_QUOTA_ERROR, '1');
        } else {
            return new JsonResponse(['error' => 'Invalid error type'], 400);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Turn PrestaShop's webservice on from the store-access modal (config page
     * or drawer), so the merchant never leaves the page for AdminWebservice.
     *
     * We write PS_WEBSERVICE directly rather than going through the core's
     * WebserviceConfiguration data provider: that service is private and its
     * only public alias is deprecated since 9.0, whereas the PS_WEBSERVICE key
     * is the stable contract the core itself reads/writes — and the one our
     * getConfigStatus() already checks.
     */
    public function enableWebserviceAction(): JsonResponse
    {
        \Configuration::updateValue('PS_WEBSERVICE', 1);
        \Configuration::updateValue(PsAskAiConfig::CONFIG_WEBSERVICE_ENABLED_ONCE, '1');

        return new JsonResponse(['success' => true]);
    }

    /**
     * Turn maintenance mode off (PE-1766). Called from the config-page banner
     * and the drawer maintenance CTA so merchants don't have to go through
     * the Shop Parameters > Maintenance page.
     */
    public function disableMaintenanceAction(): JsonResponse
    {
        if (!\Configuration::updateValue('PS_SHOP_ENABLE', '1')) {
            return new JsonResponse(['error' => 'Could not disable maintenance mode.'], 500);
        }

        return new JsonResponse(['ok' => true]);
    }

    public function deleteAction(Request $request): JsonResponse
    {
        $threadId = $this->nullIfEmpty((string) $request->query->get('thread', ''));
        if ($threadId === null) {
            return new JsonResponse(['error' => 'Missing thread'], 400);
        }

        $conversation = $this->resolveConversationForCurrentEmployee($threadId);
        if ($conversation === null) {
            return new JsonResponse(['error' => 'Conversation not found or not accessible.'], 404);
        }

        $this->conversationService->archive($conversation);

        return new JsonResponse(null, 204);
    }

    public function titleAction(Request $request): JsonResponse
    {
        $threadId = $this->nullIfEmpty((string) $request->query->get('thread', ''));
        if ($threadId === null) {
            return new JsonResponse(['error' => 'Missing thread'], 400);
        }

        $conversation = $this->resolveConversationForCurrentEmployee($threadId);
        if ($conversation === null) {
            return new JsonResponse(['error' => 'Conversation not found or not accessible.'], 404);
        }

        if ($conversation->getTitle() !== null) {
            return new JsonResponse(['title' => $conversation->getTitle()]);
        }

        $messages = $this->neuronAiService->getHistory($threadId);
        if (empty($messages)) {
            return new JsonResponse(['error' => 'No messages to title'], 422);
        }

        $provider = (string) \Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
        [$modelKey, $apiKey] = $this->resolveModelAndKey(null, null);

        try {
            $title = $this->neuronAiService->generateTitle($messages, $provider, $modelKey, $apiKey);
        } catch (\Throwable $e) {
            $this->logger?->warning('Title generation failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->sentryReporter?->captureWarning(
                'Title generation failed: ' . $e->getMessage(),
                'title_generation_failed',
                ['thread_id' => $threadId],
            );

            return new JsonResponse(['error' => 'Title generation failed'], 502);
        }

        if ($title === '') {
            return new JsonResponse(['error' => 'Empty title generated'], 422);
        }

        $this->conversationService->setTitle($conversation, $title);

        return new JsonResponse(['title' => $title]);
    }

    public function suggestionsAction(): JsonResponse
    {
        // Pick the catalog language from the logged-in employee, not the
        // browser. ISO code comes straight from PrestaShop's language table;
        // restrict to letters to keep the column-lookup key well-formed.
        $employee = \Context::getContext()->employee ?? null;
        $iso = $employee !== null ? \Language::getIsoById((int) $employee->id_lang) : false;
        $lang = is_string($iso) ? strtolower($iso) : 'en';
        if (!preg_match('/^[a-z]{2,3}$/', $lang)) {
            $lang = 'en';
        }
        try {
            return new JsonResponse($this->suggestionsService->get($lang));
        } catch (\Throwable $e) {
            $this->logger?->warning(
                'Suggestions fetch failed: ' . $e->getMessage(),
                ['exception' => $e],
            );
            $this->sentryReporter?->captureWarning(
                'Suggestions fetch failed: ' . $e->getMessage(),
                'suggestions_fetch_failed',
                ['lang' => $lang],
            );

            return new JsonResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * Validate the configured (or request-supplied) API key against the
     * provider's models endpoint. Returns 200 {"valid": true} on success,
     * 400 with {"error": "..."} on invalid key or misconfiguration.
     *
     * Optional query params: `provider`, `api_key` — when absent, both fall
     * back to the stored Configuration values (same resolution as streamAction).
     */
    public function testKeyAction(Request $request): JsonResponse
    {
        $provider = $this->nullIfEmpty((string) $request->query->get('provider', ''))
            ?? $this->nullIfEmpty((string) \Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER));

        if ($provider === null || !in_array($provider, PsAskAiConfig::SUPPORTED_PROVIDERS, true)) {
            return new JsonResponse(['error' => 'No provider configured or provider not supported.'], 400);
        }

        // Reaching test-key means the merchant submitted a key from the config
        // page (this route is only called from that form). Event 3.
        $this->segmentService?->track(
            PsAskAiConfig::SEGMENT_EVENT_API_KEY_SUBMITTED,
            ['provider' => $provider]
        );

        $apiKey = $this->nullIfEmpty((string) $request->query->get('api_key', ''))
            ?? $this->nullIfEmpty((string) \Configuration::get(PsAskAiConfig::CONFIG_API_KEY));

        try {
            $this->neuronAiService->validateApiKey($provider, $apiKey);
        } catch (\Throwable $e) {
            // The silent pre-save connection test failed → the config can't be
            // saved. Event 4, with a machine-readable error_type.
            $errorType = $e instanceof ApiKeyValidationException
                ? $e->errorType
                : ApiKeyValidationException::ERROR_UNKNOWN;
            $this->segmentService?->track(PsAskAiConfig::SEGMENT_EVENT_CONFIG_SAVE_FAILED, [
                'provider' => $provider,
                'model' => $this->resolveModelForTracking($provider),
                'error_type' => $errorType,
            ]);

            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse(['valid' => true]);
    }

    /**
     * Record a configuration-page interaction event. The only client-supplied
     * value honoured is the provider (validated against the supported list);
     * every other property is assembled server-side by SegmentService, so the
     * endpoint can't be abused to relay arbitrary analytics. Event 2.
     */
    public function trackAction(Request $request): JsonResponse
    {
        $event = (string) $request->request->get('event', '');
        if ($event !== 'provider_selected') {
            return new JsonResponse(['error' => 'Unknown event.'], 400);
        }

        $provider = (string) $request->request->get('provider', '');
        if (!in_array($provider, PsAskAiConfig::SUPPORTED_PROVIDERS, true)) {
            return new JsonResponse(['error' => 'Provider not supported.'], 400);
        }

        $this->segmentService?->track(
            PsAskAiConfig::SEGMENT_EVENT_PROVIDER_SELECTED,
            ['provider' => $provider]
        );

        return new JsonResponse(['ok' => true]);
    }

    /**
     * Best-effort model key for analytics: the configured model when set,
     * otherwise the provider's default. Never throws.
     */
    private function resolveModelForTracking(string $provider): string
    {
        $configured = $this->nullIfEmpty((string) \Configuration::get(PsAskAiConfig::CONFIG_MODEL_KEY));
        if ($configured !== null) {
            return $configured;
        }

        try {
            $storedKey = $this->nullIfEmpty((string) \Configuration::get(PsAskAiConfig::CONFIG_API_KEY));

            return $this->neuronAiService->getDefaultModelKey($provider, $storedKey) ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Flatten NeuronAI Message objects into a UI-friendly transcript.
     * Each entry has { role: "user" | "assistant" | "tool", text, name?, images?, model? }.
     * Tool-call/tool-result messages are tagged with the tool name — raw
     * (`name`) plus the module/tool split from McpToolName::describe(); the
     * frontend renders them as historical tool.request bubbles.
     * User messages with attached images carry an images[] of URL strings.
     * User messages tagged with the turn's model metadata carry its label in
     * model — the frontend replays model dividers (and switches) from it.
     *
     * @param array<int, object> $messages
     *
     * @return array<int, array<string, mixed>> entries: {role, text, name?, tool?, module?, moduleLabel?, approved?, reason?, inputs?, result?, resultTruncated?, images?, model? }
     */
    private function shapeHistory(array $messages): array
    {
        $out = [];
        $messageList = array_values($messages);
        $total = count($messageList);
        for ($i = 0; $i < $total; ++$i) {
            $message = $messageList[$i];

            // Tool results are internal plumbing — skip them, they are not user-visible.
            if ($message instanceof \NeuronAI\Chat\Messages\ToolResultMessage) {
                continue;
            }

            $role = method_exists($message, 'getRole') ? (string) $message->getRole() : '';
            $text = $this->extractDisplayText($message);

            if ($message instanceof \NeuronAI\Chat\Messages\ToolCallMessage) {
                // A tool-call message can carry text alongside its calls, and
                // can hold several calls. Emit the text and one entry per tool
                // so nothing is lost on replay; only the silent image-gen tool
                // is filtered out (per call, never the whole message).
                if ($text !== '') {
                    $out[] = ['role' => $role, 'text' => $text];
                }

                // Build a map of tool name → result from the immediately following
                // ToolResultMessage so we can detect rejected tools (their result
                // starts with "TOOL NOT EXECUTED" set by ToolRejectionHandler).
                $resultMap = [];
                if (
                    isset($messageList[$i + 1])
                    && $messageList[$i + 1] instanceof \NeuronAI\Chat\Messages\ToolResultMessage
                ) {
                    foreach ($messageList[$i + 1]->getTools() as $rt) {
                        if (is_object($rt) && method_exists($rt, 'getName') && method_exists($rt, 'getResult')) {
                            $resultMap[(string) $rt->getName()] = (string) $rt->getResult();
                        }
                    }
                }

                foreach ($message->getTools() as $tool) {
                    if (!is_object($tool) || !method_exists($tool, 'getName')) {
                        continue;
                    }
                    $toolName = (string) $tool->getName();
                    if ($toolName === '' || $toolName === ImageGenerationTool::NAME) {
                        continue;
                    }
                    $approved = !str_starts_with($resultMap[$toolName] ?? '', 'TOOL NOT EXECUTED');
                    $rawInputs = method_exists($tool, 'getInputs') && is_array($tool->getInputs()) ? $tool->getInputs() : [];
                    [$reason, $inputs] = self::extractReason($rawInputs);
                    // A declined tool never ran — its "result" is the internal
                    // rejection notice fed back to the LLM, not real output.
                    $excerpt = $approved ? self::excerpt($resultMap[$toolName] ?? null) : ['content' => null, 'truncated' => false];
                    $out[] = array_merge(
                        ['role' => $role, 'text' => ''],
                        McpToolName::describe($toolName),
                        [
                            'approved' => $approved,
                            'reason' => $reason,
                            'inputs' => self::shapeInputs($inputs),
                            'result' => $excerpt['content'],
                            'resultTruncated' => $excerpt['truncated'],
                        ],
                    );
                }
                continue;
            }

            $entry = ['role' => $role, 'text' => $text];

            if ($role === 'user' && method_exists($message, 'getMetadata')) {
                $modelKey = $message->getMetadata(SerializableSqlChatHistory::MODEL_META_KEY);
                if (is_string($modelKey) && $modelKey !== '') {
                    $entry['model'] = $this->neuronAiService->getModelLabel($modelKey);
                }
            }

            if ($role === 'user' && method_exists($message, 'getContentBlocks')) {
                $imageUrls = [];
                foreach ($message->getContentBlocks() as $block) {
                    if (
                        $block instanceof \NeuronAI\Chat\Messages\ContentBlocks\ImageContent
                        && $block->sourceType === \NeuronAI\Chat\Enums\SourceType::URL
                    ) {
                        $imageUrls[] = $block->content;
                    }
                }
                if ($imageUrls !== []) {
                    $entry['images'] = $imageUrls;
                }
            }

            $out[] = $entry;
        }

        return $out;
    }

    /**
     * Merge variation groups (PE-1721 phase 2) into the shaped transcript. The
     * groups map is keyed by USER-message ordinal; user entries appear in the
     * same order in the shaped array, so we walk them counting user entries and
     * attach `variations` on the matching one. Non-user entries pass through.
     *
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array{index: int, count: int, threads: string[]}> $variations
     *
     * @return array<int, array<string, mixed>>
     */
    private function attachVariations(array $messages, array $variations): array
    {
        if ($variations === []) {
            return $messages;
        }
        $ordinal = 0;
        foreach ($messages as &$entry) {
            if (($entry['role'] ?? '') !== 'user') {
                continue;
            }
            if (isset($variations[$ordinal])) {
                $entry['variations'] = $variations[$ordinal];
            }
            ++$ordinal;
        }
        unset($entry);

        return $messages;
    }

    /**
     * Pull only TextContent blocks out of a message — ReasoningContent is
     * dropped so restored conversations don't replay the model's chain of
     * thought as part of the visible answer. `Message::getContent()` is not
     * usable here because it concatenates reasoning into its returned string.
     */
    private function extractDisplayText(object $message): string
    {
        if (!method_exists($message, 'getContentBlocks')) {
            return '';
        }
        $buf = '';
        foreach ($message->getContentBlocks() as $block) {
            if ($block instanceof \NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent) {
                continue;
            }
            if ($block instanceof \NeuronAI\Chat\Messages\ContentBlocks\TextContent) {
                $buf .= $block->content;
            }
        }

        return $buf;
    }

    public function streamResumeAction(Request $request): StreamedResponse
    {
        $this->warmUpSessionDependentServices();

        $workflowId = (string) $request->query->get('workflowId', '');
        $threadId = (string) $request->query->get('thread', '');
        // See streamAction: query->all($key) is SF >= 5.1 only.
        $decisionsRaw = $request->query->all()['decisions'] ?? [];
        // Resume MUST use the same provider as the original turn; the active
        // provider config is that provider (it can't change mid-conversation).
        $provider = (string) \Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
        [$modelKey, $apiKey] = $this->resolveModelAndKey(
            $this->nullIfEmpty((string) $request->query->get('model', '')),
            $this->nullIfEmpty((string) $request->query->get('api_key', '')),
        );

        // Same reasoning as streamAction: hit Doctrine before headers leave.
        $conversation = ($workflowId !== '' && $threadId !== '')
            ? $this->resolveConversationForCurrentEmployee($threadId)
            : null;
        if ($conversation !== null) {
            $this->conversationService->touch($conversation);
        }

        return $this->buildStreamResponse(function (callable $emit) use ($workflowId, $threadId, $provider, $conversation, $decisionsRaw, $modelKey, $apiKey): void {
            if ($workflowId === '') {
                $emit('error-event', ['message' => 'Missing workflowId']);

                return;
            }
            if ($threadId === '') {
                $emit('error-event', ['message' => 'Missing thread']);

                return;
            }
            if ($conversation === null) {
                $emit('error-event', ['message' => 'Conversation not found or not accessible.']);

                return;
            }

            $decisions = [];
            if (is_array($decisionsRaw)) {
                foreach ($decisionsRaw as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $decisions[] = [
                        'id' => (string) ($row['id'] ?? ''),
                        'decision' => (string) ($row['decision'] ?? 'reject'),
                        'feedback' => (string) ($row['feedback'] ?? ''),
                    ];
                }
            }

            // Delete the sidecar before resuming: if a second approval fires
            // during resume, buildApprovalEmitter will save a fresh sidecar.
            $this->neuronAiService->deletePendingApproval($threadId);
            $this->neuronAiService->streamResume(
                $workflowId,
                $decisions,
                $provider,
                $this->prepromptService->get(),
                $this->buildChunkEmitter($emit),
                static function (array $meta) use ($emit): void {
                    $emit('meta', $meta);
                },
                $this->buildApprovalEmitter($emit, $threadId),
                $modelKey,
                $apiKey,
                $conversation->getThreadId(),
            );
        }, array_filter([
            'provider' => $this->neuronAiService->getModelProvider($modelKey),
            'model' => $modelKey,
            'route' => 'resume',
        ]), array_filter([
            'thread_id' => $conversation?->getThreadId(),
        ]));
    }

    /**
     * Flag the current turn of a thread to stop. Runs in its own request while
     * the SSE worker is mid-stream; the streaming loop polls the flag and
     * breaks, keeping any partial answer (or dropping the unanswered prompt).
     * Ownership is enforced the same way as the streaming endpoints.
     */
    public function stopAction(Request $request): JsonResponse
    {
        $threadId = $this->nullIfEmpty((string) $request->query->get('thread', ''));
        if ($threadId === null) {
            return new JsonResponse(['error' => 'Missing thread'], 400);
        }

        $conversation = $this->resolveConversationForCurrentEmployee($threadId);
        if ($conversation === null) {
            return new JsonResponse(['error' => 'Conversation not found or not accessible.'], 404);
        }

        $this->neuronAiService->requestStop($conversation->getThreadId());

        return new JsonResponse(['ok' => true]);
    }

    /**
     * Install a back-office module on demand from the drawer, streaming staged
     * progress over SSE so the front can drive a progress bar.
     *
     * Query params: `module` (technical name) + `product_id` (Addons id).
     *
     * Events emitted (on top of buildStreamResponse's trailing `done`):
     *   - `progress`  {percent:int, label:string, step:string}
     *   - `complete`  {installed:bool, alreadyInstalled:bool, configurable:bool, configureUrl:?string}
     *   - `error-event` {message:string}
     */
    public function installModuleAction(Request $request): StreamedResponse
    {
        $this->warmUpSessionDependentServices();

        $moduleName = (string) $request->query->get('module', '');
        $productId = (string) $request->query->get('product_id', '');
        $installer = $this->moduleInstallerService;

        // The download must run BEFORE we open the SSE stream. MBO's download
        // path reads the PrestaShop Account token from the session, and once
        // StreamedResponse has flushed SSE headers the session is closed —
        // re-reading it triggers session_start() → "headers already sent".
        // Doing it here keeps the session-touching work while the session is
        // still open; the stream below only runs the (session-free) install.
        $preflightError = null;
        $alreadyInstalled = false;
        $source = null;

        // A technical name (folder) is required to install; a numeric product id
        // is required to download an arbitrary (searched) module. Entitlement is
        // still enforced Addons-side, so a valid name + id is a safe surface.
        if ($installer === null) {
            $preflightError = 'Module installer is unavailable.';
        } elseif ($moduleName === '' || !ctype_digit($productId)) {
            $preflightError = 'This module cannot be installed from here.';
        } elseif ($installer->isInstalled($moduleName)) {
            $alreadyInstalled = true;
        } else {
            try {
                $source = $installer->download($moduleName, (int) $productId);
            } catch (\Throwable $e) {
                $this->logger?->warning('Module download failed before stream: ' . $e->getMessage(), ['exception' => $e]);
                $preflightError = $e->getMessage();
            }
        }

        return $this->buildStreamResponse(function (callable $emit) use ($moduleName, $installer, $preflightError, $alreadyInstalled, $source): void {
            if ($preflightError !== null) {
                $emit('error-event', ['message' => $preflightError]);

                return;
            }
            if ($installer === null) {
                $emit('error-event', ['message' => 'Module installer is unavailable.']);

                return;
            }

            $emit('progress', ['percent' => 5, 'step' => 'check', 'label' => 'Checking module status']);

            if ($alreadyInstalled) {
                $emit('progress', ['percent' => 100, 'step' => 'done', 'label' => 'Already installed']);
                $emit('complete', [
                    'installed' => true,
                    'alreadyInstalled' => true,
                    'configurable' => $installer->isConfigurable($moduleName),
                    'configureUrl' => $installer->getConfigureUrl($moduleName),
                ]);

                return;
            }

            // Files are already on disk (downloaded above, before the stream).
            $emit('progress', ['percent' => 20, 'step' => 'download', 'label' => 'Downloading module']);

            // Run the actual install (DB, hooks, tabs, default config). When a
            // zip was downloaded, ModuleManager unpacks it first. This does not
            // touch the session, so it is safe to run mid-stream.
            $emit('progress', ['percent' => 55, 'step' => 'install', 'label' => 'Installing module']);
            $installed = $installer->install($moduleName, $source);

            if (!$installed) {
                $emit('error-event', ['message' => sprintf('Installation of "%s" failed.', $moduleName)]);

                return;
            }

            $emit('progress', ['percent' => 90, 'step' => 'finalize', 'label' => 'Finalizing']);

            $emit('progress', ['percent' => 100, 'step' => 'done', 'label' => 'Installed']);
            $emit('complete', [
                'installed' => true,
                'alreadyInstalled' => false,
                'configurable' => $installer->isConfigurable($moduleName),
                'configureUrl' => $installer->getConfigureUrl($moduleName),
            ]);
        });
    }

    /**
     * Enrich a recommended module with live marketplace + shop state: the
     * MBO catalog data (incl. the account's `purchased` flag) plus whether the
     * module is already installed here and its config-page URL. The drawer
     * calls this when a recommendation card mounts, so cards restored from
     * history reflect the CURRENT state, not the state at message time.
     */
    public function searchModuleAction(Request $request): JsonResponse
    {
        if ($this->moduleCatalogService === null) {
            return new JsonResponse(['error' => 'Module search is unavailable.'], 503);
        }

        $productId = (string) $request->query->get('product_id', '');
        if (!ctype_digit($productId)) {
            return new JsonResponse(['error' => 'Invalid product id.'], 400);
        }

        try {
            $module = $this->moduleCatalogService->fetchModule($productId);

            $installed = false;
            $configurable = false;
            $configureUrl = null;
            $technicalName = $module['technicalName'];
            if ($technicalName !== '' && $this->moduleInstallerService !== null) {
                $installed = $this->moduleInstallerService->isInstalled($technicalName);
                if ($installed) {
                    $configurable = $this->moduleInstallerService->isConfigurable($technicalName);
                    $configureUrl = $this->moduleInstallerService->getConfigureUrl($technicalName);
                }
            }

            return new JsonResponse($module + [
                'installed' => $installed,
                'configurable' => $configurable,
                'configureUrl' => $configureUrl,
            ]);
        } catch (\Throwable $e) {
            $this->logger?->warning('Module search failed: ' . $e->getMessage(), ['exception' => $e]);

            return new JsonResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * Pre-load services that read from the session, before we return a
     * StreamedResponse. Once the StreamedResponse begins sendContent(),
     * Symfony's SessionListener has already committed and closed the
     * session at kernel.response — any later code that calls
     * Session::get(...) (e.g. ps_mbo's AddonsUser, lazy-loaded via
     * McpServerService → ModuleManagerBuilder) tries to re-start the
     * session, which fails because SSE headers are already on the wire.
     *
     * Calling McpServerService::getMcpServerUrl() here triggers the DI
     * chain (ModuleManagerBuilder → AddonsUrlSourceRetriever →
     * AddonsDataProvider → AddonsUser) while the session is still open;
     * subsequent calls during the stream reuse the cached instances and
     * don't touch the session again.
     */
    private function warmUpSessionDependentServices(): void
    {
        try {
            $this->mcpServerService->getMcpServerUrl();
        } catch (\Throwable $e) {
            $this->logger?->warning(
                'AskAiController warm-up failed: ' . $e->getMessage(),
                ['exception' => $e],
            );
            $this->sentryReporter?->captureWarning(
                'AskAiController warm-up failed: ' . $e->getMessage(),
                'mcp_warmup_failed',
            );
        }
    }

    /**
     * Look up the requested conversation (or create a fresh one when null) and
     * enforce that it belongs to the currently authenticated employee + shop.
     * Returns null when a thread id was supplied but does not match an active
     * conversation owned by the caller — the streaming endpoint surfaces this
     * as an error to the client.
     */
    private function resolveConversationForCurrentEmployee(?string $threadId): ?PsAskAiConversation
    {
        $employee = \Context::getContext()->employee ?? null;
        $shop = \Context::getContext()->shop ?? null;
        if ($employee === null || $shop === null) {
            return null;
        }
        $idEmployee = (int) $employee->id;
        $idShop = (int) $shop->id;

        if ($threadId === null) {
            return $this->conversationService->create($idEmployee, $idShop);
        }

        $conversation = $this->conversationService->getByThreadId($threadId);
        if ($conversation === null
            || $conversation->getIdEmployee() !== $idEmployee
            || $conversation->getIdShop() !== $idShop
            || $conversation->getArchivedAt() !== null
        ) {
            return null;
        }

        return $conversation;
    }

    /**
     * Resolve a fork target (PE-1721): create a child of the source thread and
     * copy the source's message prefix (everything before the edited user
     * message at $userOrdinal) into it. Returns null when the source isn't an
     * active conversation owned by the caller — the streaming endpoint then
     * surfaces "Conversation not found" like any other bad thread id.
     */
    private function resolveForkConversation(string $sourceThreadId, int $userOrdinal): ?PsAskAiConversation
    {
        $source = $this->resolveConversationForCurrentEmployee($sourceThreadId);
        if ($source === null) {
            return null;
        }

        $fork = $this->conversationService->fork($source, $userOrdinal);
        $this->neuronAiService->copyHistoryPrefix($source->getThreadId(), $fork->getThreadId(), $userOrdinal);
        // The just-created fork is the branch the merchant is now on: point the
        // tree root at it so reopening the root resolves here (PE-1721 phase 2).
        $this->conversationService->setActiveLeaf($fork);

        return $fork;
    }

    /**
     * Point the tree root at a sibling variation the merchant navigated to
     * (PE-1721 phase 2), so the choice sticks across reloads. The leaf must be
     * an active conversation owned by the caller; anything else is rejected the
     * same way as a bad thread id elsewhere.
     */
    public function setActiveLeafAction(Request $request): JsonResponse
    {
        $leafThreadId = $this->nullIfEmpty((string) $request->request->get('leaf', ''))
            ?? $this->nullIfEmpty((string) $request->query->get('leaf', ''));
        if ($leafThreadId === null) {
            return new JsonResponse(['error' => 'Missing leaf'], 400);
        }

        $leaf = $this->resolveConversationForCurrentEmployee($leafThreadId);
        if ($leaf === null) {
            return new JsonResponse(['error' => 'Conversation not found or not accessible.'], 404);
        }

        $this->conversationService->setActiveLeaf($leaf);

        return new JsonResponse(null, 204);
    }

    /**
     * Bump the tree root's updated_at when the active conversation is a fork, so
     * the recents list (roots only, ordered by updated_at) reflects activity on
     * a branch. A no-op for a root, which touch() already bumped.
     */
    private function touchRoot(PsAskAiConversation $conversation): void
    {
        if ($conversation->isRoot()) {
            return;
        }
        $root = $this->conversationService->getByThreadId($conversation->getRootThreadId());
        if ($root !== null) {
            $this->conversationService->touch($root);
        }
    }

    /**
     * Resolve final (modelKey, apiKey) for a request. Request-supplied values
     * win; otherwise we fall back to the Configuration stored by the configure
     * form. Returning null for either lets NeuronAiService apply its own
     * defaults (default model, env-based key).
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveModelAndKey(?string $reqModel, ?string $reqKey): array
    {
        $storedKey = (string) \Configuration::get(PsAskAiConfig::CONFIG_API_KEY);
        $apiKey = $reqKey ?? ($storedKey !== '' ? $storedKey : null);

        $modelKey = $reqModel;
        if ($modelKey === null) {
            $stored = (string) \Configuration::get(PsAskAiConfig::CONFIG_MODEL_KEY);
            if ($stored !== '') {
                $modelKey = $stored;
            } else {
                $activeProvider = (string) \Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
                if ($activeProvider !== '') {
                    // No model stored yet: pick the provider's default. Pass the
                    // resolved key so the reachability probe behind this works.
                    $modelKey = $this->neuronAiService->getDefaultModelKey($activeProvider, $apiKey);
                }
            }
        }

        return [$modelKey, $apiKey];
    }

    private function nullIfEmpty(string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    /**
     * Wrap a stream-producing closure in a configured SSE response. The
     * closure receives an `emit(eventName, data)` helper and is responsible
     * for calling the service. Errors get caught, reported to Sentry (this is
     * the catch-all for every chat-turn error), surfaced as `error-event`,
     * and the stream always closes with a `done` event.
     *
     * The Sentry capture happens INSIDE the StreamedResponse callback: no
     * session/Doctrine work is allowed there (headers already sent), but
     * Sentry's HTTP transport is independent of both, so reporting is safe.
     *
     * @param array<string, string> $sentryTags e.g. provider/model/route
     * @param array<string, string> $sentryContext e.g. thread_id
     */
    private function buildStreamResponse(callable $producer, array $sentryTags = [], array $sentryContext = []): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        $response->setCallback(function () use ($producer, $sentryTags, $sentryContext): void {
            // Finish the turn even if the client disconnects (drawer closed,
            // page navigation): aborting mid-run would leave the chat history
            // without the assistant's answer — the exchange would silently
            // vanish from the thread on the next reload.
            ignore_user_abort(true);

            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            @ini_set('zlib.output_compression', '0');

            $emit = static function (string $event, array $data = []): void {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                flush();
            };

            try {
                $producer($emit);
            } catch (\Throwable $e) {
                $this->logger?->error(
                    'AskAiController stream error: ' . $e->getMessage(),
                    ['exception' => $e, 'trace' => $e->getTraceAsString()],
                );
                $this->sentryReporter?->captureException($e, 'stream_error', $sentryContext, $sentryTags);
                $emit('error-event', ['message' => $e->getMessage()]);
            }

            $emit('done');
        });

        return $response;
    }

    /**
     * Pull the synthetic `reason` argument (injected by ReasonedMcpConnector,
     * filled by the LLM) out of a tool's inputs so the UI can show it apart
     * from the real arguments.
     *
     * @param array<string, mixed> $inputs
     *
     * @return array{0: ?string, 1: array<string, mixed>} [reason, inputs without reason]
     */
    private static function extractReason(array $inputs): array
    {
        $reason = $inputs[ReasonedMcpConnector::REASON_PROPERTY] ?? null;
        unset($inputs[ReasonedMcpConnector::REASON_PROPERTY]);

        return [is_string($reason) && $reason !== '' ? $reason : null, $inputs];
    }

    /**
     * Empty PHP arrays JSON-encode as [] — force {} so the frontend always
     * receives an object for tool inputs.
     *
     * @param array<string, mixed> $inputs
     */
    private static function shapeInputs(array $inputs): array|\stdClass
    {
        return $inputs === [] ? new \stdClass() : $inputs;
    }

    /**
     * @return array{content: string, truncated: bool}
     */
    private static function excerpt(?string $raw): array
    {
        $formatted = self::formatToolResult($raw);
        if (strlen($formatted) <= self::RESULT_PREVIEW_MAX_BYTES) {
            return ['content' => $formatted, 'truncated' => false];
        }

        // mb_strcut: byte-bounded but never splits a UTF-8 sequence.
        return ['content' => mb_strcut($formatted, 0, self::RESULT_PREVIEW_MAX_BYTES), 'truncated' => true];
    }

    /**
     * neuron-ai's Tool::setResult() JSON-encodes array results wholesale; an
     * MCP tool call result is an array of content blocks
     * (`[{"type":"text","text":"..."}]`), so ToolInterface::getResult() comes
     * back as that array re-encoded as one JSON string — and the block's
     * `text` is itself usually JSON (our PHP tools return structured data).
     * Unwrap both layers and pretty-print so the UI shows readable JSON
     * instead of an escaped, doubly-encoded blob (literal `\n` / `\"`).
     * Falls back to the raw string, unchanged, at the first sign it isn't
     * shaped the way we expect.
     */
    private static function formatToolResult(?string $raw): string
    {
        if ($raw === null || $raw === '') {
            return (string) $raw;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            return $raw;
        }

        if (is_array($decoded) && array_is_list($decoded)) {
            $texts = [];
            foreach ($decoded as $block) {
                if (is_array($block) && ($block['type'] ?? null) === 'text' && isset($block['text'])) {
                    $texts[] = (string) $block['text'];
                }
            }
            if ($texts !== []) {
                $decoded = implode("\n\n", $texts);
            }
        }

        if (is_string($decoded)) {
            $inner = json_decode($decoded, true);
            if (json_last_error() === \JSON_ERROR_NONE && (is_array($inner) || is_object($inner))) {
                return (string) json_encode($inner, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
            }

            return $decoded;
        }

        return (string) json_encode($decoded, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return callable(StreamChunk): void
     */
    private function buildChunkEmitter(callable $emit): callable
    {
        return static function (StreamChunk $chunk) use ($emit): void {
            if ($chunk instanceof ReasoningChunk) {
                $emit('reasoning', ['content' => $chunk->content]);

                return;
            }
            if ($chunk instanceof TextChunk) {
                $emit('text', ['content' => $chunk->content]);

                return;
            }
            if ($chunk instanceof ToolCallChunk) {
                if ($chunk->tool->getName() === ImageGenerationTool::NAME) {
                    return;
                }
                [$reason, $inputs] = self::extractReason($chunk->tool->getInputs());
                $emit('tool-call', array_merge(
                    ['id' => $chunk->tool->getCallId()],
                    McpToolName::describe($chunk->tool->getName()),
                    ['reason' => $reason, 'inputs' => self::shapeInputs($inputs)],
                ));

                return;
            }
            if ($chunk instanceof ToolResultChunk) {
                if ($chunk->tool->getName() === ImageGenerationTool::NAME) {
                    return;
                }
                $excerpt = self::excerpt($chunk->tool->getResult());
                // isError mirrors ToolErrorHandler's payload prefix, so the
                // frontend can react to a failed tool (e.g. the marketplace
                // search) without parsing the result body.
                $emit('tool-result', array_merge(
                    ['id' => $chunk->tool->getCallId()],
                    McpToolName::describe($chunk->tool->getName()),
                    [
                        'result' => $excerpt['content'],
                        'resultTruncated' => $excerpt['truncated'],
                        'isError' => str_starts_with((string) $chunk->tool->getResult(), '{"status":"error"'),
                    ],
                ));
            }
        };
    }

    /**
     * @return callable(string, Action[]): void
     */
    private function buildApprovalEmitter(callable $emit, string $threadId): callable
    {
        return function (string $workflowId, array $actions) use ($emit, $threadId): void {
            $payload = [];
            $sidecar = [];
            foreach ($actions as $action) {
                $described = McpToolName::describe($action->name);
                // neuron-ai's ToolApproval::createAction carries the tool inputs
                // only as pretty-printed JSON in Action::description
                // ('(no arguments)' when empty) — that string is the sole channel
                // the interrupt exposes them through, so we decode it back here.
                // Invalid JSON (the empty-args sentinel) decodes to null → no inputs.
                $decoded = json_decode((string) $action->description, true);
                [$reason, $inputs] = self::extractReason(is_array($decoded) ? $decoded : []);
                $entry = array_merge(
                    ['id' => $action->id],
                    $described,
                    ['reason' => $reason, 'inputs' => self::shapeInputs($inputs)],
                );
                $payload[] = $entry;
                // Sidecar carries the same fields so the approval UI restored
                // after a reload renders identically to the live one.
                $sidecar[] = $entry;
            }
            $this->neuronAiService->savePendingApproval($threadId, $workflowId, $sidecar);
            $emit('approval-needed', [
                'workflowId' => $workflowId,
                'actions' => $payload,
            ]);
        };
    }
}
