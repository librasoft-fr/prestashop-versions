<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpServer\Server;

use PrestaShop\Module\PsMcpServer\Http\HttpConstants;
use PrestaShop\Module\PsMcpServer\Services\McpAllowedUsersService;
use PrestaShop\Module\PsMcpServer\Services\McpService;
use PrestaShop\Module\PsMcpServer\Tracker\Segment;
use PrestaShop\PrestaShop\Core\Domain\Module\Exception\ModuleException;
use PsMcpServerDeps\Mcp\Server\Session\SessionStoreInterface;
use PsMcpServerDeps\Mcp\Server\Transport\BaseTransport;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InMemoryTransport extends BaseTransport
{
    private const METHOD_TOOLS_CALL = 'tools/call';

    private Segment $segment;
    private TransportAuthenticator $authenticator;
    private TransportHelper $helper;
    private bool $useSSE = false;
    private ?string $authenticatedUserRole = null;
    private ?object $parsedMessage = null;

    private array $corsHeaders;

    public function __construct(
        private readonly ServerRequestInterface $request,
        ?LoggerInterface $logger = null,
        private readonly ?SessionStoreInterface $sessionStore = null,
    ) {
        parent::__construct($logger);
        $sessionIdString = $this->request->getHeaderLine('Mcp-Session-Id');
        $this->sessionId = $sessionIdString ? Uuid::fromString($sessionIdString) : null;

        $context = \Context::getContext();

        if ($context === null) {
            throw new \PrestaShopException('Context is not defined');
        }

        $this->segment = new Segment($context);
        $this->authenticator = new TransportAuthenticator($this->logger);
        $this->helper = new TransportHelper();

        $this->corsHeaders = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Mcp-Session-Id, Mcp-Protocol-Version, Last-Event-ID, Authorization, Accept',
            'Access-Control-Expose-Headers' => 'Mcp-Session-Id',
            'Mcp-protocol-version' => '2025-11-25',
        ];
    }

    public function initialize(): void
    {
    }

    public function listen(): mixed
    {
        $this->useSSE = false;

        if ($this->request->getMethod() !== 'OPTIONS') {
            $this->checkAuthorization();
        }

        $acceptHeader = $this->request->getHeaderLine('Accept') ?: null;

        if (isset($acceptHeader) && str_contains($acceptHeader, HttpConstants::CONTENT_TYPE_SSE)) {
            $this->useSSE = true;
        }

        $this->addHeaders();

        switch ($this->request->getMethod()) {
            case 'POST':
                $this->handlePost();
                break;
            case 'OPTIONS':
                $this->handleOptions();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                $this->handleUnsupportedRequest();
                break;
        }

        $this->sessionId = null;

        return null;
    }

    private function handlePost(): void
    {
        $contentTypeHeader = $this->request->getHeaderLine('Content-Type') ?: null;

        if (isset($contentTypeHeader) && !str_contains($contentTypeHeader, HttpConstants::CONTENT_TYPE_JSON)) {
            $this->sendInvalidRequestError(415, 'Unsupported Media Type: Content-Type must be application/json');
        }

        foreach ($this->helper->getPendingNotifications() as $method) {
            $this->sendNotification($method);
        }

        $this->helper->trackFirstUse($this->segment);

        try {
            $message = (string) $this->request->getBody();

            if (empty($message)) {
                $this->sendInvalidRequestError(400, 'Empty request body');
            }

            $messageDecoded = $this->parsedMessage ?? json_decode((string) $message);

            if (($messageDecoded->method ?? '') === self::METHOD_TOOLS_CALL) {
                $depError = $this->helper->checkDependencies();
                if ($depError !== null) {
                    $this->sendToolError($depError, $messageDecoded->id ?? null);

                    return;
                }
            }

            if ($this->sessionId === null && ($messageDecoded->method ?? '') !== 'initialize') {
                $this->ensureSessionExists();
            }

            $this->handleMessage((string) $message, $this->sessionId);

            $this->handleMethodSpecifics($messageDecoded);
        } catch (\Throwable $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $this->logger->error('Failed to parse MCP message from POST body', ['error' => $e->getMessage()]);
            $this->sendInvalidRequestError(400, 'Invalid JSON: ' . $e->getMessage());
        }

        $outgoingMessages = $this->getOutgoingMessages($this->sessionId);

        foreach ($outgoingMessages as $outgoingMessage) {
            $this->send($outgoingMessage['message'], $outgoingMessage['context']);
        }
    }

    private function handleDelete(): void
    {
        http_response_code(204);
        $this->handleSessionEnd($this->sessionId);
    }

    private function handleOptions(): void
    {
        http_response_code(204);
    }

    private function handleUnsupportedRequest(): void
    {
        $this->sendInvalidRequestError(405, 'Method not allowed: ' . $this->request->getMethod());
    }

    public function close(): void
    {
        flush();
        exit;
    }

    private function checkAuthorization(): void
    {
        if (
            (bool) \Configuration::get('PS_MCP_SERVER_AUTH_DISABLED') && defined('_PS_MCP_SERVER_ALLOW_INSECURE_MODE_') && constant('_PS_MCP_SERVER_ALLOW_INSECURE_MODE_') === true
        ) {
            return;
        }

        $authHeader = (string) $this->request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            $this->sendUnauthorizeError('Missing Authorization header.');
        }

        $this->logger->info('Authorization header received', [
            'type' => str_contains($authHeader, 'Bearer') ? 'Bearer' : 'Other',
        ]);

        $mcpModule = \Module::getInstanceByName('ps_mcp_server');

        $mcpAllowedUsersService = $mcpModule->getService(McpAllowedUsersService::class);

        $authToken = null;

        if (stripos($authHeader, 'Bearer ') === 0) {
            $authToken = substr($authHeader, 7);
        } elseif (stripos($authHeader, 'Basic ') === 0) {
            $authToken = substr($authHeader, 6);
        }

        if ($authToken !== null) {
            $role = $this->authenticator->tryOAuthAuth($authToken, $mcpAllowedUsersService)
                ?? $this->authenticator->tryTokenAuth($authToken, $mcpAllowedUsersService);

            if ($role === null) {
                $this->sendUnauthorizeError('Unauthorized: Invalid token.');
            }

            $this->authenticatedUserRole = $role;

            $this->checkMemberPermission();
        } else {
            $this->sendUnauthorizeError('Unsupported Authorization scheme. Use Bearer or Basic.');
        }
    }

    private function sendNotification(string $method): void
    {
        $notification = (string) json_encode([
            'jsonrpc' => '2.0',
            'method' => $method,
        ]);

        $this->send($notification, []);
    }

    private function handleMethodSpecifics(object $messageDecoded): void
    {
        $method = $messageDecoded->method ?? '';

        if ($method === 'initialize' && $this->sessionId !== null) {
            header('Mcp-Session-Id: ' . $this->sessionId->toRfc4122());

            return;
        }

        if ($method === self::METHOD_TOOLS_CALL) {
            $toolName = $messageDecoded->params->name ?? 'unknown';
            $this->segment->trackMessage('Tool Used', [
                'tool_name' => $toolName,
                'module_name' => explode('-', $toolName, 2)[0],
            ]);

            return;
        }

        if ($method === 'prompts/get') {
            $promptName = $messageDecoded->params->name ?? 'unknown';
            $this->segment->trackMessage('Prompt Used', [
                'prompt_name' => $promptName,
                'module_name' => explode('-', $promptName, 2)[0],
            ]);

            return;
        }

        if ($method === 'resources/read') {
            $resourceUri = $messageDecoded->params->uri ?? 'unknown';
            $this->segment->trackMessage('Resource Used', [
                'resource_uri' => $resourceUri,
            ]);
        }
    }

    private function sendToolError(string $errorMessage, mixed $requestId = null): void
    {
        $message = (string) json_encode([
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'content' => [
                [
                    'type' => 'text',
                    'text' => $errorMessage,
                ],
            ],
            'isError' => true,
        ]);

        http_response_code(200);
        $this->send($message, []);
    }

    private function ensureSessionExists(): void
    {
        if ($this->sessionStore === null) {
            throw new ModuleException(sprintf('Session store is required to create a session for sessionless requests from tools like ChatGPT. Please provide a SessionStoreInterface implementation when constructing %s.', self::class));
        }

        $newSessionId = Uuid::v4();
        $this->sessionStore->write($newSessionId, '{}');
        $this->sessionId = $newSessionId;
        $this->logger->debug('Created synthetic session for sessionless request', [
            'session_id' => $newSessionId->toRfc4122(),
        ]);
    }

    private function sendInvalidRequestError(int $code, string $message): void
    {
        $message = (string) json_encode([
            'jsonrpc' => '2.0',
            'id' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ]);

        http_response_code($code);
        $this->send($message, []);
        $this->close();
    }

    private function checkMemberPermission(): void
    {
        if ($this->authenticatedUserRole === 'editor') {
            return;
        }

        $body = (string) $this->request->getBody();
        $message = json_decode($body);
        $this->parsedMessage = $message;

        if (!isset($message->method)) {
            $this->sendForbiddenError('Forbidden: Missing method in request.');
        }

        $allowedMethods = [
            'initialize',
            'ping',
            'notifications/initialized',
            'tools/list',
            'prompts/list',
            'prompts/get',
            'resources/list',
            'resources/templates/list',
            'resources/read',
            'completions/complete',
        ];

        if (in_array($message->method, $allowedMethods, true)) {
            return;
        }

        if ($message->method === self::METHOD_TOOLS_CALL && isset($message->params->name)) {
            $mcpModule = \Module::getInstanceByName('ps_mcp_server');

            $mcpService = $mcpModule->getService(McpService::class);

            if (!$mcpService->isToolReadOnly($message->params->name)) {
                $this->sendForbiddenError('Forbidden: Viewer role cannot execute write tools.');
            }

            return;
        }

        $this->sendForbiddenError('Forbidden: Viewer role cannot call method ' . $message->method . '.');
    }

    private function sendForbiddenError(string $errorMessage): void
    {
        $this->logger->warning('Forbidden access attempt', ['error' => $errorMessage]);

        $message = (string) json_encode([
            'jsonrpc' => '2.0',
            'id' => null,
            'error' => [
                'code' => 403,
                'message' => $errorMessage,
            ],
        ]);

        $this->addHeaders();
        header(HttpConstants::JSON_CONTENT_TYPE_HEADER);
        http_response_code(403);
        $this->send($message, []);
        $this->close();
    }

    private function sendUnauthorizeError(string $errorMessage): void
    {
        $context = \Context::getContext();
        $shopContext = $context ? $context->shop : null;
        $baseUrl = $shopContext ? $shopContext->getBaseURL(true) : '';

        $this->logger->warning('Unauthorized access attempt', ['error' => $errorMessage]);

        $message = (string) json_encode([
            'jsonrpc' => '2.0',
            'id' => null,
            'error' => [
                'code' => 401,
                'message' => $errorMessage,
            ],
        ]);

        $this->addHeaders();

        $authDisabled = (bool) \Configuration::get('PS_MCP_SERVER_AUTH_DISABLED');
        if (!$authDisabled) {
            header('WWW-Authenticate: Bearer resource_metadata="' . $baseUrl . '.well-known/oauth-protected-resource", scope="mcp.discover mcp.read mcp.write email" realm="mcp"');
        }

        header(HttpConstants::JSON_CONTENT_TYPE_HEADER);
        http_response_code(401);
        $this->send($message, []);
        $this->close();
    }

    protected function addHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        foreach ($this->corsHeaders as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($this->useSSE) {
            header(HttpConstants::SSE_CONTENT_TYPE_HEADER);
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no');
        } else {
            header(HttpConstants::JSON_CONTENT_TYPE_HEADER);
        }
    }

    public function send(string $data, array $context): void
    {
        if (isset($context['session_id'])) {
            $this->sessionId = $context['session_id'];
        }

        if ($this->useSSE) {
            echo 'data: ' . $data . "\n\n";
        } else {
            echo $data;
        }
    }
}
