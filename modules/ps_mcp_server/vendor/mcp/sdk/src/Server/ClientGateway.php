<?php



namespace Mcp\Server;

use Mcp\Exception\ClientException;
use Mcp\Exception\InvalidArgumentException;
use Mcp\Exception\RuntimeException;
use Mcp\Schema\Content\AudioContent;
use Mcp\Schema\Content\Content;
use Mcp\Schema\Content\ImageContent;
use Mcp\Schema\Content\SamplingMessage;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Elicitation\ElicitationSchema;
use Mcp\Schema\Enum\LoggingLevel;
use Mcp\Schema\Enum\Role;
use Mcp\Schema\Enum\SamplingContext;
use Mcp\Schema\JsonRpc\Error;
use Mcp\Schema\JsonRpc\Notification;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\ModelPreferences;
use Mcp\Schema\Notification\LoggingMessageNotification;
use Mcp\Schema\Notification\ProgressNotification;
use Mcp\Schema\Request\CreateSamplingMessageRequest;
use Mcp\Schema\Request\ElicitRequest;
use Mcp\Schema\Result\CreateSamplingMessageResult;
use Mcp\Schema\Result\ElicitResult;
use Mcp\Server\Session\SessionInterface;


class ClientGateway
{
    public function __construct(
        private readonly SessionInterface $session,
    ) {
    }

    
    public function notify(Notification $notification): void
    {
        \Fiber::suspend([
            'type' => 'notification',
            'notification' => $notification,
            'session_id' => $this->session->getId()->toRfc4122(),
        ]);
    }

    
    public function log(LoggingLevel $level, mixed $data, ?string $logger = null): void
    {
        $this->notify(new LoggingMessageNotification($level, $data, $logger));
    }

    
    public function progress(float $progress, ?float $total = null, ?string $message = null): void
    {
        $meta = $this->session->get(Protocol::SESSION_ACTIVE_REQUEST_META, []);
        $progressToken = $meta['progressToken'] ?? null;

        if (null === $progressToken) {
            
            return;
        }

        $this->notify(new ProgressNotification($progressToken, $progress, $total, $message));
    }

    
    public function sample(array|Content|string $message, int $maxTokens = 1000, int $timeout = 120, array $options = []): CreateSamplingMessageResult
    {
        $preferences = $options['preferences'] ?? null;
        if (null !== $preferences && !$preferences instanceof ModelPreferences) {
            throw new InvalidArgumentException('The "preferences" option must be an array or an instance of ModelPreferences.');
        }

        if (\is_string($message)) {
            $message = new TextContent($message);
        }
        if (\is_object($message) && \in_array($message::class, [TextContent::class, AudioContent::class, ImageContent::class], true)) {
            $message = [new SamplingMessage(Role::User, $message)];
        }

        $request = new CreateSamplingMessageRequest(
            messages: $message,
            maxTokens: $maxTokens,
            preferences: $preferences,
            systemPrompt: $options['systemPrompt'] ?? null,
            includeContext: $options['includeContext'] ?? null,
            temperature: $options['temperature'] ?? null,
            stopSequences: $options['stopSequences'] ?? null,
            metadata: $options['metadata'] ?? null,
        );

        $response = $this->request($request, $timeout);

        if ($response instanceof Error) {
            throw new ClientException($response);
        }

        return CreateSamplingMessageResult::fromArray($response->result);
    }

    
    public function elicit(string $message, ElicitationSchema $requestedSchema, int $timeout = 120): ElicitResult
    {
        $request = new ElicitRequest($message, $requestedSchema);

        $response = $this->request($request, $timeout);

        if ($response instanceof Error) {
            throw new ClientException($response);
        }

        return ElicitResult::fromArray($response->result);
    }

    
    public function supportsElicitation(): bool
    {
        $capabilities = $this->session->get('client_capabilities', []);

        
        return \array_key_exists('elicitation', $capabilities);
    }

    
    private function request(Request $request, int $timeout = 120): Response|Error
    {
        $response = \Fiber::suspend([
            'type' => 'request',
            'request' => $request,
            'session_id' => $this->session->getId()->toRfc4122(),
            'timeout' => $timeout,
        ]);

        if (!$response instanceof Response && !$response instanceof Error) {
            throw new RuntimeException('Transport returned an unexpected payload; expected a Response or Error message.');
        }

        return $response;
    }
}
