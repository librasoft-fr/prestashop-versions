<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service;

use NeuronAI\Agent\Agent;
use NeuronAI\Agent\Middleware\ToolApproval;
use NeuronAI\Agent\Nodes\ToolNode;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\History\SQLChatHistory;
use NeuronAI\Chat\Messages\ContentBlocks\ContentBlockInterface;
use NeuronAI\Chat\Messages\ContentBlocks\ImageContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Stream\Chunks\StreamChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\ToolCallChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\ToolResultChunk;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Mistral\Mistral;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\OpenAI\Responses\OpenAIResponses;
use NeuronAI\Tools\ProviderTool;
use NeuronAI\Workflow\Interrupt\Action;
use NeuronAI\Workflow\Interrupt\ApprovalRequest;
use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;
use NeuronAI\Workflow\Persistence\FilePersistence;
use NeuronAI\Workflow\Persistence\PersistenceInterface;
use PrestaShop\Module\PsAskAi\Config\PsAskAiConfig;
use PrestaShop\Module\PsAskAi\Exception\ApiKeyValidationException;
use PrestaShop\Module\PsAskAi\Service\Agent\ToolErrorHandler;
use PrestaShop\Module\PsAskAi\Service\Audit\ToolActionLogger;
use PrestaShop\Module\PsAskAi\Service\Chat\SerializableSqlChatHistory;
use PrestaShop\Module\PsAskAi\Service\Mcp\LoopbackHttpTransport;
use PrestaShop\Module\PsAskAi\Service\Mcp\ReasonedMcpConnector;
use PrestaShop\Module\PsAskAi\Service\Mcp\ToolApprovalPolicy;
use PrestaShop\Module\PsAskAi\Tools\ImageGenerationTool;
use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;

class NeuronAiService
{
    private const CHAT_HISTORY_TABLE_SUFFIX = 'ps_ask_ai_chat_history';

    /** Provider model-listing cache TTL (seconds) — see listReachableModels(). */
    private const MODELS_CACHE_TTL_SECONDS = 600;

    /** Providers that have a dedicated image-generation model. */
    private const IMAGE_GENERATION_PROVIDERS = ['gemini', 'openai'];

    public function __construct(
        private readonly McpServerService $mcpServerService,
        private readonly LlmParamsService $llmParamsService,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?SentryReporter $sentryReporter = null,
    ) {
    }

    /**
     * Models offered by the chat UI: the intersection of the operator-curated
     * whitelist ({@see LlmParamsService::getModelKeys()}) and the models the
     * configured API key can actually reach ({@see listReachableModels()}). A
     * model is offered only when it appears in BOTH. The result is inherently
     * scoped to $provider because that provider's live /models endpoint only
     * returns its own ids. Fail-closed: an empty list when the live probe fails.
     *
     * @return array<string, array{label: string, provider: string}>
     */
    public function getAvailableModels(string $provider, ?string $apiKey = null): array
    {
        $whitelist = $this->llmParamsService->getModelKeys();
        if ($whitelist === []) {
            return [];
        }

        try {
            $reachable = $this->listReachableModels($provider, $apiKey);
        } catch (\Throwable $e) {
            // Fail-closed: a failed /models probe means we can't confirm what
            // the key reaches, so offer nothing rather than a stale guess.
            $this->logger?->warning(
                sprintf('listReachableModels(%s) failed, offering no models: %s', $provider, $e->getMessage()),
                ['exception' => $e]
            );

            return [];
        }
        $reachableSet = array_flip($reachable);

        $out = [];
        foreach ($whitelist as $key) {
            if (!isset($reachableSet[$key])) {
                continue;
            }
            $out[$key] = [
                'label' => $this->llmParamsService->getLabelForModel($key) ?? $key,
                'provider' => $provider,
            ];
        }

        return $out;
    }

    /**
     * Default model to pre-select for a provider: the operator-flagged default
     * (`"default": true` in llm_params) if it's actually reachable for this
     * provider, otherwise the first offered model. Null when nothing is
     * reachable. Correct-by-construction — the returned key always belongs to
     * $provider because it's drawn from {@see getAvailableModels()}, which
     * intersects the whitelist with the provider's live /models list. This
     * relies on that (file-cached) probe, so it is NOT network-free; it's only
     * reached on the no-model-selected cold path and for best-effort analytics.
     */
    public function getDefaultModelKey(string $provider, ?string $apiKey = null): ?string
    {
        $available = $this->getAvailableModels($provider, $apiKey);
        if ($available === []) {
            return null;
        }

        foreach ($this->llmParamsService->getDefaultFlaggedKeys() as $flagged) {
            if (isset($available[$flagged])) {
                return $flagged;
            }
        }

        return array_key_first($available);
    }

    /**
     * Human-readable label for a model key. Falls back to the key itself for
     * models no longer in the catalog, so old threads keep a readable value.
     */
    public function getModelLabel(string $modelKey): string
    {
        return $this->llmParamsService->getLabelForModel($modelKey) ?? $modelKey;
    }

    /**
     * Provider id ('gemini', 'anthropic', …) behind a model key. Derived from
     * the key prefix — the key IS the wire-level model id, so the prefix maps
     * 1:1 to a provider. Used to tag Sentry events; null for unknown/empty keys
     * so the tag is simply omitted.
     */
    public function getModelProvider(?string $modelKey): ?string
    {
        if ($modelKey === null || $modelKey === '') {
            return null;
        }

        return match (true) {
            str_starts_with($modelKey, 'gemini') => 'gemini',
            str_starts_with($modelKey, 'claude') => 'anthropic',
            str_starts_with($modelKey, 'gpt') => 'openai',
            str_starts_with($modelKey, 'mistral') => 'mistral',
            default => null,
        };
    }

    /**
     * Full curated whitelist of chat-capable model keys (all providers), with no
     * reachability check and no network call. Used to validate a model selection:
     * a key only needs to be a known chat model, since the provider is configured
     * separately.
     *
     * @return list<string>
     */
    public function getModelKeys(): array
    {
        return $this->llmParamsService->getModelKeys();
    }

    /**
     * Model ids the given provider's API key can reach, from the provider's
     * live models-listing endpoint. Ids are normalized (Gemini's "models/"
     * prefix stripped) so they compare exactly against curated keys.
     *
     * $apiKey follows the usual resolution (explicit → BO Configuration →
     * env): callers pass the shop's configured key so this works whether the
     * key lives in Configuration or the environment.
     *
     * Result is file-cached per provider for MODELS_CACHE_TTL_SECONDS to keep
     * the drawer-open path off the network on every request. Pass
     * $useCache = false to always probe live and skip read+write — used by
     * validateApiKey to test a *candidate* key (a cache entry from a different
     * key would otherwise falsely validate it).
     *
     * @return list<string>
     *
     * @throws ApiKeyValidationException on connect/auth failure or unparseable body
     */
    public function listReachableModels(string $provider, ?string $apiKey = null, bool $useCache = true): array
    {
        $this->ensureEnvLoaded();

        $cachePath = $this->modelsCachePath($provider);
        if ($useCache) {
            $cached = is_file($cachePath) ? @file_get_contents($cachePath) : false;
            $age = is_file($cachePath) ? time() - (int) @filemtime($cachePath) : PHP_INT_MAX;
            if ($cached !== false && $cached !== '' && $age < self::MODELS_CACHE_TTL_SECONDS) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return array_values(array_filter($decoded, 'is_string'));
                }
            }
        }

        $envVar = PsAskAiConfig::PROVIDER_API_KEY_ENV[$provider] ?? null;
        if ($envVar === null) {
            throw new \RuntimeException(sprintf('Unknown provider: %s', $provider));
        }
        $key = $this->resolveApiKey($apiKey, $envVar);

        [$url, $headers] = $this->buildProbeRequest($provider, $key);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_TIMEOUT => 10,
            \CURLOPT_HTTPHEADER => $headers,
            \CURLOPT_FAILONERROR => false,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $connectFailed = $error !== '' || $status === 0;
        if ($connectFailed) {
            throw new ApiKeyValidationException("We couldn't connect to the provider. Check your network or try again later.", ApiKeyValidationException::fromProbe($status, true));
        }
        if ($status < 200 || $status >= 300) {
            throw new ApiKeyValidationException("We couldn't connect with this API key. Check your key or try again later.", ApiKeyValidationException::fromProbe($status, false));
        }

        $ids = $this->parseModelIds($provider, is_string($body) ? $body : '');
        if ($useCache) {
            $this->writeModelsCache($cachePath, $ids);
        }

        return $ids;
    }

    /**
     * Extract normalized model ids from a provider's models-list response body.
     *
     * @return list<string>
     *
     * @throws ApiKeyValidationException when the body isn't the expected JSON shape
     */
    private function parseModelIds(string $provider, string $body): array
    {
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new ApiKeyValidationException('Provider models response was not valid JSON.', ApiKeyValidationException::ERROR_UNKNOWN);
        }

        $ids = [];
        if ($provider === 'gemini') {
            // { "models": [ { "name": "models/gemini-3.5-flash", ... } ] }
            foreach ($data['models'] ?? [] as $entry) {
                $name = is_array($entry) ? ($entry['name'] ?? null) : null;
                if (is_string($name) && $name !== '') {
                    $ids[] = str_starts_with($name, 'models/') ? substr($name, strlen('models/')) : $name;
                }
            }
        } else {
            // Anthropic / OpenAI / Mistral: { "data": [ { "id": "...", ... } ] }
            foreach ($data['data'] ?? [] as $entry) {
                $id = is_array($entry) ? ($entry['id'] ?? null) : null;
                if (is_string($id) && $id !== '') {
                    $ids[] = $id;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private function modelsCachePath(string $provider): string
    {
        $base = defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/';

        $safe = (string) preg_replace('/[^a-z0-9_-]/i', '', $provider);

        return rtrim((string) $base, '/') . '/ps_ask_ai/provider_models_' . $safe . '.json';
    }

    /**
     * @param list<string> $ids
     */
    private function writeModelsCache(string $path, array $ids): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }
        // Write-temp-then-rename so concurrent readers never see a partial file.
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        if (@file_put_contents($tmp, json_encode($ids)) === false) {
            return;
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
        }
    }

    /**
     * Probe the provider's models list endpoint with the given API key.
     * Throws a descriptive RuntimeException when the key is invalid or
     * the provider is unreachable. Returns void on success.
     *
     * @throws \RuntimeException on invalid key, connection failure, or unknown provider
     */
    public function validateApiKey(string $provider, ?string $apiKey): void
    {
        // Probe the provider's models endpoint; success (no throw) means the
        // key reached the provider. The listed ids are discarded here — the
        // model picker fetches them via listReachableModels() separately.
        // useCache: false so we test THIS candidate key live, never a cached
        // result from a previously-configured key.
        $this->listReachableModels($provider, $apiKey, useCache: false);
    }

    /**
     * Build the (url, headers) pair for a provider key probe request.
     *
     * @return array{0: string, 1: list<string>}
     */
    private function buildProbeRequest(string $provider, string $apiKey): array
    {
        return match ($provider) {
            'gemini' => [
                'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($apiKey),
                [],
            ],
            'anthropic' => [
                'https://api.anthropic.com/v1/models',
                ['x-api-key: ' . $apiKey, 'anthropic-version: 2023-06-01'],
            ],
            'openai' => [
                'https://api.openai.com/v1/models',
                ['Authorization: Bearer ' . $apiKey],
            ],
            'mistral' => [
                'https://api.mistral.ai/v1/models',
                ['Authorization: Bearer ' . $apiKey],
            ],
            default => throw new \RuntimeException(sprintf('Unknown provider: %s', $provider)),
        };
    }

    /**
     * @param (callable(ApprovalRequest): void)|null $approveTools Called when the agent pauses for tool approval. The callback must mark each Action approved or rejected via $action->approve() / $action->reject() before returning. When null, no approval middleware is attached and tools run freely.
     */
    public function ask(
        string $prompt,
        string $provider,
        ?string $threadId = null,
        ?string $instructions = null,
        ?callable $approveTools = null,
        ?string $modelKey = null,
        ?string $apiKey = null,
    ): string {
        $agent = $this->buildAgent($provider, $threadId, $instructions, modelKey: $modelKey, apiKey: $apiKey);

        // Read-only, closed-world MCP tools are safe to auto-execute; gate only
        // the rest. An empty list would mean "gate everything", so when nothing
        // needs approval we skip the middleware (and its persistence) entirely.
        $gated = $approveTools !== null ? ToolApprovalPolicy::gatedToolNames($agent->getTools()) : [];
        if ($gated !== []) {
            $agent->addMiddleware(ToolNode::class, new ToolApproval($gated));

            // Default InMemoryPersistence serializes the interrupt graph, which
            // walks into SQLChatHistory and trips on its PDO. We stay in the
            // same PHP process, so store the live object instead of serializing.
            $agent->setPersistence(new class implements PersistenceInterface {
                /** @var array<string, WorkflowInterrupt> */
                private array $storage = [];

                public function save(string $workflowId, WorkflowInterrupt $interrupt): void
                {
                    $this->storage[$workflowId] = $interrupt;
                }

                public function load(string $workflowId): WorkflowInterrupt
                {
                    return $this->storage[$workflowId];
                }

                public function delete(string $workflowId): void
                {
                    unset($this->storage[$workflowId]);
                }
            });
        }

        // Audit trail (PE-1724): same observer as the streaming path — ask()
        // executes MCP tools too. In-process approval keeps the workflowId
        // stable, so registration order doesn't matter here.
        $agent->observe($this->makeToolActionLogger($threadId));

        $messages = $this->buildTurnMessage($agent, $prompt, modelKey: $modelKey);
        $resume = null;

        while (true) {
            try {
                $message = $agent->chat($messages, $resume)->getMessage();

                return (string) $message->getContent();
            } catch (WorkflowInterrupt $interrupt) {
                $request = $interrupt->getRequest();

                if ($approveTools === null || !$request instanceof ApprovalRequest) {
                    throw $interrupt;
                }

                $approveTools($request);

                // Resume the same agent instance with the decided request.
                // No new user message: chat([], $request) picks up where we paused.
                $messages = [];
                $resume = $request;
            }
        }
    }

    /**
     * Streaming variant of ask().
     *
     * Every chunk is forwarded to $onChunk; the accumulated text content is
     * returned at the end. $onMeta fires once after agent build with tool
     * metadata.
     *
     * If $onApproval is provided, ToolApproval middleware is installed and
     * file-based persistence is set up so the workflow can survive across
     * HTTP requests. When the agent interrupts for approval, $onApproval is
     * called with (workflowId, Action[]) and stream() returns; the caller is
     * expected to gather user decisions and call streamResume().
     *
     * @param (callable(StreamChunk): void)|null $onChunk
     * @param (callable(array{tools: string[]}): void)|null $onMeta
     * @param (callable(string, Action[]): void)|null $onApproval
     */
    /**
     * @param string[] $imageUrls public URLs of images to attach to the user message (URL source type)
     */
    public function stream(
        string $prompt,
        string $provider,
        ?string $threadId = null,
        ?string $instructions = null,
        ?callable $onChunk = null,
        ?callable $onMeta = null,
        ?callable $onApproval = null,
        bool $urlContext = false,
        ?string $modelKey = null,
        ?string $apiKey = null,
        array $imageUrls = [],
    ): string {
        $agent = $this->buildAgent(
            $provider,
            $threadId,
            $instructions,
            attachMcp: !$urlContext,
            urlContext: $urlContext,
            modelKey: $modelKey,
            apiKey: $apiKey,
        );

        // Collect gated MCP tool names BEFORE adding the local image-generation
        // tool — ImageGenerationTool has no MCP annotations so ToolApprovalPolicy
        // would gate it, but it should run freely without human approval.
        $gated = [];
        if (!$urlContext && $onApproval !== null) {
            $gated = ToolApprovalPolicy::gatedToolNames($agent->getTools());
        }

        $localToolNames = [];
        if (!$urlContext) {
            $imageTool = $this->makeImageGenerationTool($provider, $apiKey);
            if ($imageTool !== null) {
                $agent->addTool($imageTool);
                $localToolNames[] = $imageTool->getName();
            }
        }

        if ($gated !== []) {
            $agent->addMiddleware(ToolNode::class, new ToolApproval($gated));
            $agent->setPersistence($this->resolveInterruptPersistence());
        }

        // Audit trail (PE-1724): record every tool execution — MCP and local —
        // for this employee. Registered under the agent's workflowId, so it must
        // come after any setPersistence() call that could change that id.
        $agent->observe($this->makeToolActionLogger($threadId));

        // Wrap $onChunk to suppress tool-call/tool-result events for local tools
        // (e.g. ImageGenerationTool) so they run silently with no UI bubble.
        if ($onChunk !== null && $localToolNames !== []) {
            $originalOnChunk = $onChunk;
            $onChunk = static function (StreamChunk $chunk) use ($originalOnChunk, $localToolNames): void {
                if ($chunk instanceof ToolCallChunk && in_array($chunk->tool->getName(), $localToolNames, true)) {
                    return;
                }
                if ($chunk instanceof ToolResultChunk && in_array($chunk->tool->getName(), $localToolNames, true)) {
                    return;
                }
                $originalOnChunk($chunk);
            };
        }

        if ($onMeta !== null) {
            $names = [];
            foreach ($agent->getTools() as $tool) {
                if ($tool instanceof ProviderTool) {
                    // ProviderTool::getName() may be null; the meaningful id
                    // is the type (e.g. urlContext, googleSearch).
                    $names[] = '[' . $tool->getType() . ']';
                    continue;
                }
                if (is_object($tool) && method_exists($tool, 'getName')) {
                    $name = (string) $tool->getName();
                    if ($name !== '') {
                        $names[] = $name;
                    }
                }
            }
            $onMeta(['tools' => $names]);
        }

        $message = $this->buildTurnMessage($agent, $prompt, $imageUrls, $modelKey);

        // Drop any stale stop flag from a previous turn/race so it can't abort
        // this fresh turn before it starts.
        if ($threadId !== null) {
            $this->clearStop($threadId);
        }

        $text = '';
        $stopped = false;
        try {
            foreach ($agent->stream($message)->events() as $event) {
                if ($threadId !== null && $this->isStopRequested($threadId)) {
                    $this->clearStop($threadId);
                    $stopped = true;
                    break;
                }
                // NeuronAI's OpenAI Chat Completions provider has a base
                // `processToolCallDelta()` that does `yield;` (with no value),
                // injecting a stray `null` into the event stream whenever the
                // model begins a tool call. Skip those — they're not real chunks.
                if (!$event instanceof StreamChunk) {
                    continue;
                }
                if ($onChunk !== null) {
                    $onChunk($event);
                }
                if ($event instanceof TextChunk) {
                    $text .= $event->content;
                }
            }
        } catch (WorkflowInterrupt $interrupt) {
            $request = $interrupt->getRequest();
            if ($onApproval === null || !$request instanceof ApprovalRequest) {
                throw $interrupt;
            }
            // Remember which tools were gated for THIS workflow so streamResume
            // can rebuild the same ToolApproval policy. Without it the resumed
            // run would gate every tool (empty list = gate all), forcing
            // approval prompts for tools that ran freely on the initial turn.
            $this->saveGatedTools($interrupt->getWorkflowId(), $gated);
            $onApproval($interrupt->getWorkflowId(), $request->getActions());
        }

        // User stopped the turn: neuron-ai never appended the assistant reply,
        // so close out the turn ourselves (keep the partial answer, or drop the
        // unanswered prompt) before the thread is reloaded.
        if ($stopped) {
            $history = $agent->getChatHistory();
            if ($history instanceof SerializableSqlChatHistory) {
                $history->finalizeStoppedTurn($text);
            }
        }

        return $text;
    }

    /**
     * Resume a previously paused stream after the operator has decided each
     * pending tool call.
     *
     * @param array<int, array{id: string, decision: string, feedback?: string}> $decisions
     * @param (callable(StreamChunk): void)|null $onChunk
     * @param (callable(array{tools: string[]}): void)|null $onMeta
     * @param (callable(string, Action[]): void)|null $onApproval
     */
    public function streamResume(
        string $workflowId,
        array $decisions,
        string $provider,
        ?string $instructions = null,
        ?callable $onChunk = null,
        ?callable $onMeta = null,
        ?callable $onApproval = null,
        ?string $modelKey = null,
        ?string $apiKey = null,
        ?string $threadId = null,
    ): string {
        // Skip MCP attach on resume: the persisted tool instances already
        // carry their original MCP token. Re-attaching would call
        // McpServerService::getMcpToken() which regenerates and invalidates
        // the persisted token, causing 401 on the resumed tool call.
        $agent = $this->buildAgent($provider, null, $instructions, false, modelKey: $modelKey, apiKey: $apiKey);
        // Re-install the same approval policy as the original turn (persisted
        // at interrupt time). Falling back to the no-args ToolApproval would
        // gate EVERY tool on the resumed run — including read-only MCP tools
        // and the local image-generation tool that ran without approval before
        // the interrupt. Missing sidecar (stale cache cleanup) fails closed.
        $gated = $this->loadGatedTools($workflowId);
        $agent->addMiddleware(ToolNode::class, $gated !== null ? new ToolApproval($gated) : new ToolApproval());

        $persistence = $this->resolveInterruptPersistence();
        $agent->setPersistence($persistence, $workflowId);

        // Audit trail (PE-1724): same observer as stream(), plus the human
        // decisions so gated tools get an exact approved/rejected attribution
        // instead of the string-sniffing fallback. Must come after
        // setPersistence($persistence, $workflowId) — observe() registers
        // under the workflowId that call just restored.
        $agent->observe($this->makeToolActionLogger($threadId, $this->approvalDecisionMap($decisions)));

        // Load the persisted interrupt, apply human decisions to its actions.
        $persisted = $persistence->load($workflowId);
        $request = $persisted->getRequest();
        if (!$request instanceof ApprovalRequest) {
            throw new \RuntimeException('Persisted interrupt is not an approval request.');
        }
        foreach ($decisions as $decision) {
            $action = $request->getAction((string) ($decision['id'] ?? ''));
            if (!$action instanceof Action) {
                continue;
            }
            if (($decision['decision'] ?? '') === 'approve') {
                $action->approve();
            } else {
                $action->reject(($decision['feedback'] ?? '') !== '' ? (string) $decision['feedback'] : null);
            }
        }

        // No meta emit on resume: we didn't re-attach tools, so $agent->getTools()
        // would be empty and the frontend would falsely render "no tools attached".
        // The persisted tool instances still run normally during resume.
        unset($onMeta);

        if ($threadId !== null) {
            $this->clearStop($threadId);
        }

        $text = '';
        $stopped = false;
        try {
            foreach ($agent->stream([], $request)->events() as $event) {
                if ($threadId !== null && $this->isStopRequested($threadId)) {
                    $this->clearStop($threadId);
                    $stopped = true;
                    break;
                }
                // See note in stream(): the OpenAI Chat Completions stream
                // can emit a null event when a tool call delta arrives.
                if (!$event instanceof StreamChunk) {
                    continue;
                }
                if ($onChunk !== null) {
                    $onChunk($event);
                }
                if ($event instanceof TextChunk) {
                    $text .= $event->content;
                }
            }
        } catch (WorkflowInterrupt $interrupt) {
            $nextRequest = $interrupt->getRequest();
            if ($onApproval === null || !$nextRequest instanceof ApprovalRequest) {
                throw $interrupt;
            }
            // Another approval round — same wire format as the first time.
            // Carry the original gated list forward for the next resume.
            if ($gated !== null) {
                $this->saveGatedTools($interrupt->getWorkflowId(), $gated);
            }
            $onApproval($interrupt->getWorkflowId(), $nextRequest->getActions());

            return $text;
        }

        // User stopped the resumed turn: close it out like stream() does. The
        // workflow's chat history is restored onto the agent during resume, so
        // getChatHistory() is the thread's SerializableSqlChatHistory.
        if ($stopped) {
            $history = $agent->getChatHistory();
            if ($history instanceof SerializableSqlChatHistory) {
                $history->finalizeStoppedTurn($text);
            }
            $this->deleteGatedTools($workflowId);

            return $text;
        }

        // Turn finished without another interrupt — the sidecar is spent.
        $this->deleteGatedTools($workflowId);

        return $text;
    }

    /**
     * The ToolApproval policy (list of gated tool names) is request state that
     * must survive the approval HTTP round-trip alongside the workflow file.
     * Stored as a JSON sidecar keyed by workflowId in the same cache dir as
     * FilePersistence. The id is hashed before use in a filename because it
     * arrives via the resume request's query string.
     *
     * @param string[] $gated
     */
    private function saveGatedTools(string $workflowId, array $gated): void
    {
        @file_put_contents($this->gatedToolsPath($workflowId), json_encode(array_values($gated)));
    }

    /**
     * @return string[]|null null when no sidecar exists for this workflow
     */
    private function loadGatedTools(string $workflowId): ?array
    {
        $raw = @file_get_contents($this->gatedToolsPath($workflowId));
        if ($raw === false) {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        return array_values(array_filter(array_map('strval', $decoded), static fn (string $name): bool => $name !== ''));
    }

    private function deleteGatedTools(string $workflowId): void
    {
        @unlink($this->gatedToolsPath($workflowId));
    }

    private function gatedToolsPath(string $workflowId): string
    {
        $dir = (defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/') . 'ps_ask_ai';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . '/gated_tools_' . md5($workflowId) . '.json';
    }

    /**
     * Persist {workflowId, actions[]} keyed by threadId so the frontend can
     * restore a pending approval after navigating away and returning. Each
     * action carries {id, name, tool?, module?, moduleLabel?, reason?, inputs?}
     * — the same shape buildApprovalEmitter sends over SSE.
     *
     * @param array<int, array<string, mixed>> $actions
     */
    public function savePendingApproval(string $threadId, string $workflowId, array $actions): void
    {
        $written = @file_put_contents($this->pendingApprovalPath($threadId), json_encode([
            'workflowId' => $workflowId,
            'actions' => $actions,
        ]));
        if ($written === false) {
            // The approval survives this request either way (the frontend got
            // the SSE event), but a lost sidecar means the approval UI can't be
            // restored after navigation — worth knowing about.
            $this->sentryReporter?->captureWarning(
                'Failed to persist approval sidecar',
                'approval_sidecar_write_failed',
                ['thread_id' => $threadId, 'workflow_id' => $workflowId],
            );
        }
    }

    /**
     * @return array{workflowId: string, actions: array<int, array<string, mixed>>}|null
     */
    public function loadPendingApproval(string $threadId): ?array
    {
        $raw = @file_get_contents($this->pendingApprovalPath($threadId));
        if ($raw === false) {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['workflowId'], $decoded['actions'])) {
            return null;
        }

        return $decoded;
    }

    public function deletePendingApproval(string $threadId): void
    {
        @unlink($this->pendingApprovalPath($threadId));
    }

    private function pendingApprovalPath(string $threadId): string
    {
        $dir = (defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/') . 'ps_ask_ai';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . '/pending_approval_' . md5($threadId) . '.json';
    }

    /**
     * Build the per-turn audit observer (PE-1724). Employee/shop come from the
     * BO context — the same source the controllers use — and fall back to 0
     * outside a BO session (chat-test sandbox, CLI).
     *
     * @param array<string, string> $approvalDecisions callId => approved|rejected
     */
    private function makeToolActionLogger(?string $threadId, array $approvalDecisions = []): ToolActionLogger
    {
        $context = \Context::getContext();

        return new ToolActionLogger(
            idEmployee: isset($context->employee) ? (int) $context->employee->id : 0,
            idShop: isset($context->shop) ? (int) $context->shop->id : 0,
            threadId: $threadId,
            approvalDecisions: $approvalDecisions,
        );
    }

    /**
     * Flatten the streamResume() decision payloads into callId => verdict for
     * exact approval attribution in the audit log.
     *
     * @param array<int, array{id?: string, decision?: string, feedback?: string}> $decisions
     *
     * @return array<string, string>
     */
    private function approvalDecisionMap(array $decisions): array
    {
        $map = [];
        foreach ($decisions as $decision) {
            $id = (string) ($decision['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $map[$id] = ($decision['decision'] ?? '') === 'approve'
                ? ToolActionLogger::APPROVAL_APPROVED
                : ToolActionLogger::APPROVAL_REJECTED;
        }

        return $map;
    }

    /**
     * File-based persistence used by the SSE path to survive the
     * interrupt → user-decision → resume HTTP round-trip.
     */
    private function resolveInterruptPersistence(): FilePersistence
    {
        $dir = (defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/') . 'ps_ask_ai';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Cannot create interrupt persistence dir: %s', $dir));
        }

        return new FilePersistence($dir);
    }

    /**
     * Flag a thread's in-flight turn to stop. Written by the stop endpoint
     * (a separate request/worker) while the SSE worker is mid-stream; the
     * streaming loop polls isStopRequested() between events and breaks.
     *
     * The session is committed/closed at kernel.response before the SSE body
     * runs (see AskAiController::warmUpSessionDependentServices), so this
     * request never blocks on the stream's session lock.
     */
    public function requestStop(string $threadId): void
    {
        @file_put_contents($this->stopFlagPath($threadId), '1');
    }

    private function isStopRequested(string $threadId): bool
    {
        return is_file($this->stopFlagPath($threadId));
    }

    private function clearStop(string $threadId): void
    {
        @unlink($this->stopFlagPath($threadId));
    }

    private function stopFlagPath(string $threadId): string
    {
        $dir = (defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/') . 'ps_ask_ai';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . '/stop_' . md5($threadId) . '.flag';
    }

    /**
     * Fold an unanswered previous-turn prompt into the current one.
     *
     * When a turn fails or is abandoned before the assistant replies,
     * SerializableSqlChatHistory drops the orphan user message at load and
     * hands us its text. Rather than lose that question, we combine it with the
     * current prompt so a single turn answers both:
     *
     *  - no orphan / dedup / guard → the current prompt alone, not merged;
     *  - otherwise                 → orphan and current prompt concatenated.
     *
     * A merged message is tagged (metadata, invisible to the LLM) so a merge
     * that fails again is dropped next time instead of snowballing.
     *
     * Any URL-sourced images for this turn are attached as ImageContent blocks
     * alongside the (possibly folded) text.
     *
     * The orphan fold is a no-op unless the agent carries our
     * SerializableSqlChatHistory (threaded turns only; the resume path attaches
     * no SQL history).
     *
     * @param string[] $imageUrls
     */
    private function buildTurnMessage(Agent $agent, string $prompt, array $imageUrls = [], ?string $modelKey = null): UserMessage
    {
        $history = $agent->getChatHistory();
        $orphan = $history instanceof SerializableSqlChatHistory
            ? $history->consumePendingOrphan()
            : null;

        [$text, $merged] = self::mergeOrphanPrompt(
            $orphan['text'] ?? null,
            $prompt,
            $orphan['merged'] ?? false,
        );

        if ($imageUrls === []) {
            $message = new UserMessage($text);
        } else {
            $blocks = [new TextContent($text)];
            foreach ($imageUrls as $url) {
                $blocks[] = new ImageContent($url, SourceType::URL);
            }
            $message = new UserMessage($blocks);
        }

        if ($merged) {
            $message->addMetadata(SerializableSqlChatHistory::MERGED_META_KEY, '1');
        }

        // Tag the turn with the effective model so the history endpoint can
        // show which model each exchange ran on (and where it changed).
        // createProvider() (via buildAgent) already ran and threw on a
        // null/empty key, so a real key is expected here; guard anyway to match
        // the reader's is_string && !== '' check in AskAiController::shapeHistory.
        if ($modelKey !== null && $modelKey !== '') {
            $message->addMetadata(SerializableSqlChatHistory::MODEL_META_KEY, $modelKey);
        }

        return $message;
    }

    /**
     * Pure merge policy (no I/O, so unit-testable). Returns the text to send
     * and whether it is a fresh merge (→ caller tags it):
     *
     *  - no orphan                          → prompt as-is, not merged;
     *  - orphan already a merged prompt     → prompt as-is (guard): never merge
     *    twice, so consecutive failures can't snowball the text;
     *  - orphan identical to current prompt → prompt as-is (dedup): covers
     *    auto-resume (reload re-sends the same text) and exact retries;
     *  - otherwise                          → "<orphan>\n\n<current>", merged.
     *
     * @return array{0: string, 1: bool} [text to send, is a fresh merge]
     */
    public static function mergeOrphanPrompt(?string $orphan, string $prompt, bool $alreadyMerged): array
    {
        if ($orphan === null || trim($orphan) === '' || $alreadyMerged || trim($orphan) === trim($prompt)) {
            return [$prompt, false];
        }

        return [$orphan . "\n\n" . $prompt, true];
    }

    /**
     * Build a fully-configured Agent (provider + MCP tools + chat history +
     * instructions). Shared by ask() and stream().
     *
     * Set $attachMcp=false when resuming a persisted workflow: the persisted
     * tool instances already carry the original MCP token, and re-attaching
     * here would regenerate the token and 401 the resumed tool call.
     *
     * Set $urlContext=true to enable Gemini's url_context provider tool so
     * the model fetches URLs the user pasted into the prompt. Gemini does
     * NOT allow function tools (our MCP) alongside provider tools, so the
     * caller is expected to also set $attachMcp=false in that case.
     */
    private function buildAgent(
        string $provider,
        ?string $threadId,
        ?string $instructions,
        bool $attachMcp = true,
        bool $urlContext = false,
        ?string $modelKey = null,
        ?string $apiKey = null,
    ): Agent {
        $this->ensureEnvLoaded();

        $aiProvider = $this->createProvider($modelKey, $provider, $apiKey);
        // Keep $agent typed as the concrete Agent (Agent::make() returns static):
        // chaining ->setAiProvider() would widen it to AgentInterface, whose
        // addTool() signature doesn't include ProviderToolInterface.
        $agent = Agent::make();
        $agent->setAiProvider($aiProvider);

        // Without this, neuron-ai's ToolNode re-throws any exception a tool
        // raises (e.g. an MCP transport/server error), aborting the whole run
        // and tearing down the SSE stream. Returning a string turns the failure
        // into the tool's result so the model sees it and can recover.
        //
        // Must be an invokable class, not a closure: the ToolApproval interrupt
        // serializes the ToolNode (which holds this handler) to disk, and PHP
        // refuses to serialize closures.
        $agent->toolErrorHandler(new ToolErrorHandler($this->logger, $this->sentryReporter));

        // Attach MCP tools before setting instructions so we can detect whether
        // tools are actually available and include a proactive-use hint in the
        // system prompt. Without this, the LLM answers from general knowledge
        // on the first turn (e.g. a suggestion click) instead of querying the
        // store — it only uses tools when the user explicitly asks about them.
        if ($attachMcp) {
            $this->attachMcpTools($agent);
        }

        if ($urlContext) {
            // Gemini's tools array uses camelCase keys for provider tools
            // (cf. googleSearch, codeExecution). The Python SDK exposes
            // url_context as snake_case but the REST payload is urlContext.
            $agent->addTool(ProviderTool::make('urlContext'));
        }

        $datetime = (new \DateTimeImmutable())->format('Y-m-d H:i:s T');
        $datetimeHint = "\n\nCurrent date and time: {$datetime}";

        // When MCP tools loaded successfully, instruct the model to use them
        // proactively from the very first message rather than falling back to
        // general knowledge for PrestaShop-specific questions.
        $toolHint = '';
        if ($attachMcp && $agent->getTools() !== []) {
            $toolHint = "\n\nYou have MCP tools connected to the merchant's PrestaShop store. Always use them proactively to fetch real, up-to-date store data before answering — including on the very first message of a conversation. Do not rely solely on general knowledge for store-specific queries.";
        }

        // Module recommendation protocol lives in the remote preprompt
        // (fetched + cached by PrepromptService), not here — see
        // askai_preprompt.md Rule 4 and the "Module recommendation" section.

        $agent->setInstructions(($instructions ?? '') . $datetimeHint . $toolHint);

        if ($threadId !== null) {
            // SerializableSqlChatHistory: same behaviour as SQLChatHistory but
            // its __serialize/__unserialize drop and rebuild the PDO so the
            // workflow interrupt can be persisted to file across requests.
            $agent->setChatHistory(new SerializableSqlChatHistory(
                thread_id: $threadId,
                pdo: $this->getPdo(),
                table: $this->getChatHistoryTableName(),
                sanitizeForProvider: $provider,
            ));
        }

        return $agent;
    }

    private function createProvider(?string $modelKey, string $provider, ?string $apiKey = null): AIProviderInterface
    {
        $key = $modelKey;
        if ($key === null || $key === '') {
            throw new \RuntimeException('No model key resolved for the active provider.');
        }

        // The catalog is the operator-curated whitelist; a key not in it is
        // not a chat model we're willing to run.
        if (!in_array($key, $this->llmParamsService->getModelKeys(), true)) {
            throw new \RuntimeException(sprintf('Unknown model key: %s', $key));
        }

        // Per-model overrides from the remote JSON. Empty array if the model
        // isn't listed there — providers fall back to their own defaults. The
        // key IS the wire-level model id sent to the provider. Provider mode
        // (Anthropic thinking, OpenAI Responses vs Chat) is inferred from which
        // reasoning params the entry carries.
        $params = $this->llmParamsService->getParamsForModel($key);
        $anthropicThinking = isset($params['thinking_budget_tokens']) || isset($params['thinking_effort']);
        $openAiReasoning = isset($params['reasoning_summary']) || isset($params['thinking_effort']);

        return match ($provider) {
            'gemini' => $this->createGeminiProvider($key, $apiKey, $params),
            'anthropic' => $this->createAnthropicProvider($key, $apiKey, $anthropicThinking, $params),
            'openai' => $openAiReasoning
                ? $this->createOpenAIResponsesProvider($key, $apiKey, true, $params)
                : $this->createOpenAIProvider($key, $apiKey, $params),
            'mistral' => $this->createMistralProvider($key, $apiKey, $params),
            default => throw new \RuntimeException(sprintf('Unsupported provider type: %s', $provider)),
        };
    }

    /**
     * Pick the user-supplied key when set, otherwise fall back to the env
     * variable. Empty strings count as "not provided" so an empty form field
     * doesn't override a valid env value.
     */
    private function resolveApiKey(?string $explicit, string $envVar): string
    {
        if ($explicit !== null && $explicit !== '') {
            return $explicit;
        }
        $fromEnv = getenv($envVar);
        if ($fromEnv === false || $fromEnv === '') {
            throw new \RuntimeException(sprintf('%s is not set and no API key was provided.', $envVar));
        }

        return $fromEnv;
    }

    /**
     * @param array<string, float|int|string|bool> $params
     */
    private function createGeminiProvider(string $model, ?string $apiKey, array $params): Gemini
    {
        $key = $this->resolveApiKey($apiKey, 'PS_ASK_AI__GEMINI_API_KEY');

        // Gemini 2.5 emits a "thought" part before the functionCall part. The
        // stock provider filters parts with array_filter, which preserves keys —
        // so tools end up keyed [1 => …] instead of [0 => …]. That non-sequential
        // array then JSON-encodes as an object on the next request and Gemini
        // rejects it ("Unknown name '1' at contents[N].parts"). Reindex here.
        // includeThoughts: ask Gemini to actually stream the reasoning text.
        // Without it the API returns only terse summaries and the
        // ReasoningChunks arrive with empty content.
        $thinkingConfig = [
            'includeThoughts' => $params['thinking_include_thoughts'] ?? true,
        ];
        if (isset($params['thinking_budget_tokens'])) {
            // Gemini 2.5: -1 = dynamic (model decides), 0 = thinking off,
            // positive int = explicit budget. No-op when value equals -1.
            $thinkingConfig['thinkingBudget'] = (int) $params['thinking_budget_tokens'];
        }
        $generation = ['thinkingConfig' => $thinkingConfig];
        if (isset($params['temperature'])) {
            $generation['temperature'] = $params['temperature'];
        }
        if (isset($params['top_p'])) {
            $generation['topP'] = $params['top_p'];
        }
        if (isset($params['top_k'])) {
            $generation['topK'] = $params['top_k'];
        }
        if (isset($params['max_tokens'])) {
            $generation['maxOutputTokens'] = (int) $params['max_tokens'];
        }
        $providerParams = ['generationConfig' => $generation];

        return new class(key: $key, model: $model, parameters: $providerParams) extends Gemini {
            /**
             * @param ContentBlockInterface[] $blocks
             * @param array<int|string, array<string, mixed>> $toolCalls
             */
            protected function createToolCallMessage(array $blocks, array $toolCalls): ToolCallMessage
            {
                return parent::createToolCallMessage($blocks, array_values($toolCalls));
            }

            /**
             * neuron-ai's HandleStream only dispatches tool calls when finishReason === 'STOP'.
             * Gemini 2.5 thinking models occasionally return UNEXPECTED_TOOL_CALL or
             * MALFORMED_FUNCTION_CALL instead. Two cases:
             *
             * 1. functionCall parts ARE present (accumulated in streamState.toolCalls):
             *    promote to ToolCallMessage so StreamingNode routes to ToolCallEvent.
             *
             * 2. functionCall parts are absent (Gemini stripped them as truly malformed):
             *    silently retry once with a correction hint — consume the retry generator
             *    without yielding so the frontend sees no double-reasoning output.
             */
            public function stream(\NeuronAI\Chat\Messages\Message ...$messages): \Generator
            {
                $generator = parent::stream(...$messages);
                yield from $generator;
                $message = $generator->getReturn();

                $geminiIncompleteToolCallReasons = ['UNEXPECTED_TOOL_CALL', 'MALFORMED_FUNCTION_CALL'];

                if (
                    $message instanceof \NeuronAI\Chat\Messages\AssistantMessage
                    && in_array($message->stopReason(), $geminiIncompleteToolCallReasons, true)
                    && isset($this->streamState)
                ) {
                    if ($this->streamState->hasToolCalls()) {
                        return $this->createToolCallMessage(
                            $this->streamState->getContentBlocks(),
                            $this->streamState->getToolCalls()
                        )->setUsage($this->streamState->getUsage());
                    }

                    $correction = new UserMessage(
                        'Your previous function call was malformed. Please try again with a valid function call.'
                    );
                    $retryMessages = [...$messages, $correction];
                    $retryGenerator = parent::stream(...$retryMessages);
                    foreach ($retryGenerator as $_) {
                        // Discard chunks — no double-reasoning in SSE output.
                    }

                    return $retryGenerator->getReturn();
                }

                return $message;
            }
        };
    }

    /**
     * @param array<string, float|int|string|bool> $params
     */
    private function createAnthropicProvider(string $model, ?string $apiKey, bool $thinking = false, array $params = []): Anthropic
    {
        $key = $this->resolveApiKey($apiKey, 'PS_ASK_AI__ANTHROPIC_API_KEY');

        if (!$thinking) {
            // Haiku 4.5 and other non-thinking Anthropic models accept
            // sampling params freely.
            $sampling = [];
            if (isset($params['temperature'])) {
                $sampling['temperature'] = $params['temperature'];
            }
            if (isset($params['top_p'])) {
                $sampling['top_p'] = $params['top_p'];
            }
            if (isset($params['top_k'])) {
                $sampling['top_k'] = $params['top_k'];
            }

            return new Anthropic(
                key: $key,
                model: $model,
                max_tokens: isset($params['max_tokens']) ? (int) $params['max_tokens'] : 8192,
                parameters: $sampling,
            );
        }

        // max_tokens includes the thinking budget on Anthropic, so bump it
        // above the default 8192 to leave room for the reply after the
        // reasoning tokens are consumed.
        $maxTokens = isset($params['max_tokens']) ? (int) $params['max_tokens'] : 16384;

        // Opus 4.7 dropped the legacy `thinking.type=enabled` knob in favor of
        // adaptive thinking driven by `output_config.effort`.
        if (str_starts_with($model, 'claude-opus-4-7')) {
            $effort = $params['thinking_effort'] ?? 'medium';

            return new Anthropic(
                key: $key,
                model: $model,
                max_tokens: $maxTokens,
                parameters: [
                    'thinking' => ['type' => 'adaptive'],
                    'output_config' => ['effort' => $effort],
                ],
            );
        }

        // Anthropic thinking-enabled models reject any temperature/top_p/top_k
        // other than the defaults — intentionally skip sampling params here.
        $budget = isset($params['thinking_budget_tokens']) ? (int) $params['thinking_budget_tokens'] : 5000;

        return new Anthropic(
            key: $key,
            model: $model,
            max_tokens: $maxTokens,
            parameters: ['thinking' => ['type' => 'enabled', 'budget_tokens' => $budget]],
        );
    }

    /**
     * @param array<string, float|int|string|bool> $params
     */
    private function createOpenAIProvider(string $model, ?string $apiKey, array $params = []): OpenAI
    {
        $sampling = [];
        if (isset($params['temperature'])) {
            $sampling['temperature'] = $params['temperature'];
        }
        if (isset($params['top_p'])) {
            $sampling['top_p'] = $params['top_p'];
        }
        if (isset($params['max_tokens'])) {
            $sampling['max_tokens'] = (int) $params['max_tokens'];
        }

        return new OpenAI(
            key: $this->resolveApiKey($apiKey, 'PS_ASK_AI__OPENAI_API_KEY'),
            model: $model,
            parameters: $sampling,
        );
    }

    /**
     * @param array<string, float|int|string|bool> $params
     */
    private function createOpenAIResponsesProvider(string $model, ?string $apiKey, bool $thinking = false, array $params = []): OpenAIResponses
    {
        // OpenAI's reasoning models only stream a *summary* of their chain of
        // thought, not the raw thoughts (unlike Gemini/Claude). `summary: auto`
        // lets the API pick the right verbosity for the chosen model.
        // GPT-5 / o-series reject temperature/top_p, so the thinking branch
        // does not forward sampling params.
        $providerParams = [];
        if ($thinking) {
            $reasoning = ['summary' => $params['reasoning_summary'] ?? 'auto'];
            if (isset($params['thinking_effort'])) {
                $reasoning['effort'] = $params['thinking_effort'];
            }
            $providerParams['reasoning'] = $reasoning;
        } else {
            if (isset($params['temperature'])) {
                $providerParams['temperature'] = $params['temperature'];
            }
            if (isset($params['top_p'])) {
                $providerParams['top_p'] = $params['top_p'];
            }
        }
        if (isset($params['max_tokens'])) {
            $providerParams['max_output_tokens'] = (int) $params['max_tokens'];
        }

        return new OpenAIResponses(
            key: $this->resolveApiKey($apiKey, 'PS_ASK_AI__OPENAI_API_KEY'),
            model: $model,
            parameters: $providerParams,
        );
    }

    /**
     * @param array<string, float|int|string|bool> $params
     */
    private function createMistralProvider(string $model, ?string $apiKey, array $params = []): Mistral
    {
        $sampling = [];
        if (isset($params['temperature'])) {
            $sampling['temperature'] = $params['temperature'];
        }
        if (isset($params['top_p'])) {
            $sampling['top_p'] = $params['top_p'];
        }
        if (isset($params['max_tokens'])) {
            $sampling['max_tokens'] = (int) $params['max_tokens'];
        }

        return new Mistral(
            key: $this->resolveApiKey($apiKey, 'PS_ASK_AI__MISTRAL_API_KEY'),
            model: $model,
            parameters: $sampling,
        );
    }

    /**
     * Build an ImageGenerationTool for the current provider, or return null if the
     * provider doesn't support image generation or if required env/constants are missing.
     */
    private function makeImageGenerationTool(string $provider, ?string $apiKey): ?ImageGenerationTool
    {
        if (!defined('_PS_TMP_IMG_DIR_')) {
            return null;
        }

        if (!in_array($provider, self::IMAGE_GENERATION_PROVIDERS, true)) {
            return null;
        }

        $envVar = match ($provider) {
            'gemini' => 'PS_ASK_AI__GEMINI_API_KEY',
            default => 'PS_ASK_AI__OPENAI_API_KEY',
        };

        try {
            $resolvedKey = $this->resolveApiKey($apiKey, $envVar);
        } catch (\RuntimeException) {
            return null;
        }

        return new ImageGenerationTool(
            provider: $provider,
            apiKey: $resolvedKey,
            saveDir: _PS_TMP_IMG_DIR_ . 'ps_ask_ai/',
            shopBaseUrl: $this->mcpServerService->getShopBaseUrl(),
        );
    }

    private function attachMcpTools(Agent $agent): void
    {
        $publicUrl = $this->mcpServerService->getMcpServerUrl();
        if ($publicUrl === null) {
            $this->logger?->warning('attachMcpTools: getMcpServerUrl() returned null (ps_mcp_server not installed/enabled?)');

            return;
        }

        $token = $this->mcpServerService->getMcpToken();
        if ($token === null) {
            $this->logger?->warning('attachMcpTools: getMcpToken() returned null (McpAllowedUsersService unavailable or returned non-array)', ['publicUrl' => $publicUrl]);

            return;
        }

        $host = parse_url($publicUrl, PHP_URL_HOST);

        // Server-to-server calls (BO PHP process talking to its own MCP endpoint)
        // can't reach the public URL — it loops out through tunnels/proxies. Force
        // DNS resolution of the shop host to 127.0.0.1 so the request stays on the
        // loopback while keeping the public Host header (PrestaShop URL routing).
        //
        // We also force the transport URL to http:// because local Apache inside
        // the container typically only listens on :80 — the tunnel does the TLS
        // termination. If the public base URL is https (the case when the BO
        // request itself came in over the tunnel), curl would try to TLS-handshake
        // against 127.0.0.1:443 and fail. PS routing only cares about the Host
        // header, not the scheme.
        //
        // Cold-start can be slow in dev mode (PrestaShop full bootstrap + OAuth
        // validator JWKs fetch) — up to ~60s observed. In prod this is much faster.
        $loopbackUrl = (string) preg_replace('#^https://#i', 'http://', $publicUrl);

        try {
            $tools = $this->fetchMcpTools([
                'url' => $publicUrl,
                'token' => $token,
                'transport' => new LoopbackHttpTransport([
                    'url' => $loopbackUrl,
                    'token' => $token,
                    'timeout' => 90,
                    'resolve' => ["$host:80:127.0.0.1"],
                ]),
            ]);

            if ($tools !== []) {
                $agent->addTool($tools);
                $this->logger?->info('attachMcpTools: attached ' . count($tools) . ' MCP tools to agent', [
                    'tools' => array_map(static fn ($tool) => $tool->getName(), $tools),
                ]);
            } else {
                $this->logger?->warning('attachMcpTools: McpConnector returned 0 tools', ['publicUrl' => $publicUrl]);
            }
        } catch (\Throwable $e) {
            $this->logger?->warning('Failed to attach MCP tools to NeuronAI agent: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->sentryReporter?->captureWarning(
                'Failed to attach MCP tools: ' . $e->getMessage(),
                'mcp_attach_failed',
            );
        }
    }

    /**
     * Thin seam around the real MCP handshake so tests can assert
     * attachMcpTools() builds the right connector config without performing
     * one. ReasonedMcpConnector = stock McpConnector + a synthetic required
     * `reason` property on every tool (filled by the LLM, shown in the
     * approval UI, stripped before the MCP server is called).
     *
     * @param array<string, mixed> $config
     *
     * @return \NeuronAI\Tools\ToolInterface[]
     */
    protected function fetchMcpTools(array $config): array
    {
        return ReasonedMcpConnector::make($config)->tools();
    }

    /**
     * @return \NeuronAI\Chat\Messages\Message[]
     */
    public function getHistory(string $threadId): array
    {
        return $this->buildHistory($threadId)->getMessages();
    }

    /**
     * Generate a short title for a conversation from its first exchange.
     * Uses an ephemeral agent — no MCP tools, no chat history stored.
     *
     * @param array<int, object> $messages
     */
    public function generateTitle(array $messages, string $provider, ?string $modelKey = null, ?string $apiKey = null): string
    {
        $this->ensureEnvLoaded();

        $context = '';
        $userSeen = false;
        foreach ($messages as $message) {
            if (!method_exists($message, 'getRole')) {
                continue;
            }
            $role = (string) $message->getRole();
            if ($role === 'user' && !$userSeen) {
                $text = $this->extractTitleText($message);
                if ($text !== '') {
                    $context .= 'User: ' . mb_substr($text, 0, 400) . "\n";
                    $userSeen = true;
                }
            } elseif ($role === 'assistant' && $userSeen) {
                $text = $this->extractTitleText($message);
                if ($text !== '') {
                    $context .= 'Assistant: ' . mb_substr($text, 0, 400) . "\n";
                }
                break;
            }
        }

        if ($context === '') {
            return '';
        }

        $aiProvider = $this->createProvider($modelKey, $provider, $apiKey);
        $agent = Agent::make();
        $agent->setAiProvider($aiProvider);
        $agent->setInstructions(
            'Generate a very short title (maximum 6 words) for a conversation. '
            . 'Reply with only the title — no quotes, no trailing punctuation, no explanations.'
        );

        $result = $agent->chat(new UserMessage("Conversation:\n{$context}"))->getMessage();

        return mb_substr(trim((string) $result->getContent()), 0, 255);
    }

    private function extractTitleText(object $message): string
    {
        if (!method_exists($message, 'getContentBlocks')) {
            return method_exists($message, 'getContent') ? (string) $message->getContent() : '';
        }
        $buf = '';
        foreach ($message->getContentBlocks() as $block) {
            if ($block instanceof TextContent) {
                $buf .= $block->content;
            }
        }

        return $buf;
    }

    /**
     * DEV/TEST ONLY. Persist a bare user message to a thread without running a
     * turn, leaving the thread ending on an unanswered user message — the exact
     * state a turn that crashed before the assistant reply would produce. Used
     * by the chat-test sandbox to exercise orphan healing / prompt folding on
     * demand. Construction heals any pre-existing orphan first, so calling it
     * twice never stacks two user messages.
     */
    public function appendOrphanUserMessage(string $threadId, string $text): void
    {
        $history = new SerializableSqlChatHistory(
            thread_id: $threadId,
            pdo: $this->getPdo(),
            table: $this->getChatHistoryTableName(),
        );
        $history->addMessage(new UserMessage($text));
    }

    /**
     * Copy the prefix of $sourceThreadId's chat history that precedes the edited
     * user message into a fresh $targetThreadId (PE-1721 fork). The source is
     * read read-only through a plain SQLChatHistory (no healing side effects);
     * the target is a SerializableSqlChatHistory whose constructor inserts its
     * row, then we overwrite it with the slice. The edited prompt is appended by
     * the normal streaming turn that runs on the target thread afterwards.
     *
     * $userOrdinal is the 0-based index, among genuine user prompts, of the
     * message being edited — stable despite tool/assistant messages in between
     * (user prompts map 1:1 to the frontend's user bubbles).
     */
    public function copyHistoryPrefix(string $sourceThreadId, string $targetThreadId, int $userOrdinal): void
    {
        $source = $this->buildHistory($sourceThreadId);
        $prefix = SerializableSqlChatHistory::prefixBeforeUserOrdinal($source->getMessages(), $userOrdinal);

        $target = new SerializableSqlChatHistory(
            thread_id: $targetThreadId,
            pdo: $this->getPdo(),
            table: $this->getChatHistoryTableName(),
        );
        $target->replaceHistory($prefix);
    }

    private function buildHistory(string $threadId): SQLChatHistory
    {
        return new SQLChatHistory(
            thread_id: $threadId,
            pdo: $this->getPdo(),
            table: $this->getChatHistoryTableName(),
        );
    }

    private function getPdo(): \PDO
    {
        return new \PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', _DB_SERVER_, _DB_NAME_),
            _DB_USER_,
            _DB_PASSWD_,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    private function getChatHistoryTableName(): string
    {
        $prefix = defined('_DB_PREFIX_') ? _DB_PREFIX_ : 'ps_';

        return $prefix . self::CHAT_HISTORY_TABLE_SUFFIX;
    }

    /**
     * The module's bootstrap.php only runs when PrestaShop instantiates the
     * Module class. In CLI/console contexts, Dotenv is never loaded, so we
     * load it here to make PS_ASK_AI__* vars available via getenv().
     */
    private function ensureEnvLoaded(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $envPath = dirname(__DIR__, 2) . '/.env';
        if (is_file($envPath) && class_exists(Dotenv::class)) {
            $dotenv = new Dotenv();
            // Dotenv::usePutenv() only exists in Symfony 5.1+ (PS9). On Symfony 4.4 (PS8)
            // putenv is enabled via the constructor and defaults to true, so skip the call.
            // @phpstan-ignore function.alreadyNarrowedType, function.impossibleType (true on PS9 / false on PS8 Symfony 4.4)
            if (method_exists($dotenv, 'usePutenv')) {
                $dotenv->usePutenv(true);
            }
            $dotenv->loadEnv($envPath);
        }
    }
}
