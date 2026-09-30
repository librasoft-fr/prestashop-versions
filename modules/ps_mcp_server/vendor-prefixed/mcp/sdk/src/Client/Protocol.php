<?php

namespace PsMcpServerDeps\Mcp\Client;

use PsMcpServerDeps\Mcp\Client\Handler\Notification\ProgressNotificationHandler;
use PsMcpServerDeps\Mcp\Client\State\ClientState;
use PsMcpServerDeps\Mcp\Client\State\ClientStateInterface;
use PsMcpServerDeps\Mcp\Client\Transport\TransportInterface;
use PsMcpServerDeps\Mcp\JsonRpc\MessageFactory;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Notification\InitializedNotification;
use PsMcpServerDeps\Mcp\Schema\Request\InitializeRequest;
use PsMcpServerDeps\Mcp\Schema\Result\InitializeResult;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Protocol
{
    private ?TransportInterface $transport = null;
    private ClientStateInterface $state;
    private MessageFactory $messageFactory;
    private LoggerInterface $logger;

    private array $notificationHandlers;

    public function __construct(
        private readonly array $requestHandlers = [],
        array $notificationHandlers = [],
        ?MessageFactory $messageFactory = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->state = new ClientState();
        $this->messageFactory = $messageFactory ?? MessageFactory::make();
        $this->logger = $logger ?? new NullLogger();

        $this->notificationHandlers = [
            new ProgressNotificationHandler($this->state),
            ...$notificationHandlers,
        ];
    }

    public function connect(TransportInterface $transport, Configuration $config): void
    {
        $this->transport = $transport;
        $transport->setState($this->state);
        $transport->onInitialize(fn () => $this->initialize($config));
        $transport->onMessage($this->processMessage(...));
        $transport->onError(fn (\Throwable $e) => $this->logger->error('Transport error', ['exception' => $e]));

        $this->logger->info('Protocol connected to transport', ['transport' => $transport::class]);
    }

    public function initialize(Configuration $config): Response|Error
    {
        $request = new InitializeRequest(
            $config->protocolVersion->value,
            $config->capabilities,
            $config->clientInfo,
        );

        $response = $this->request($request, $config->initTimeout);

        if ($response instanceof Response) {
            $initResult = InitializeResult::fromArray($response->result);
            $this->state->setServerInfo($initResult->serverInfo);
            $this->state->setInstructions($initResult->instructions);
            $this->state->setInitialized(true);

            $this->sendNotification(new InitializedNotification());

            $this->logger->info('Initialization complete', [
                'server' => $initResult->serverInfo->name,
            ]);
        }

        return $response;
    }

    public function request(Request $request, int $timeout, bool $withProgress = false): Response|Error
    {
        $requestId = $this->state->nextRequestId();
        $request = $request->withId($requestId);

        if ($withProgress) {
            $progressToken = "prog-{$requestId}";
            $request = $request->withMeta(['progressToken' => $progressToken]);
        }

        $this->state->addPendingRequest($requestId, $timeout);
        $this->sendRequest($request);

        $immediate = $this->state->consumeResponse($requestId);
        if (null !== $immediate) {
            $this->logger->debug('Received immediate response', ['id' => $requestId]);

            return $immediate;
        }

        $this->logger->debug('Suspending fiber for response', ['id' => $requestId]);

        return \Fiber::suspend([
            'type' => 'await_response',
            'request_id' => $requestId,
            'timeout' => $timeout,
        ]);
    }

    private function sendRequest(Request $request): void
    {
        $this->logger->debug('Sending request', [
            'id' => $request->getId(),
            'method' => $request::getMethod(),
        ]);

        $encoded = json_encode($request, \JSON_THROW_ON_ERROR);
        $this->transport?->send($encoded);
    }

    public function sendNotification(Notification $notification): void
    {
        $this->logger->debug('Sending notification', ['method' => $notification::getMethod()]);

        $encoded = json_encode($notification, \JSON_THROW_ON_ERROR);
        $this->transport?->send($encoded);
    }

    private function sendResponse(Response|Error $response): void
    {
        $this->logger->debug('Sending response', ['id' => $response->getId()]);

        $encoded = json_encode($response, \JSON_THROW_ON_ERROR);
        $this->transport?->send($encoded);
    }

    public function processMessage(string $input): void
    {
        $this->logger->debug('Received message', ['input' => $input]);

        try {
            $messages = $this->messageFactory->create($input);
        } catch (\JsonException $e) {
            $this->logger->warning('Failed to parse message', ['exception' => $e]);

            return;
        }

        foreach ($messages as $message) {
            if ($message instanceof Response || $message instanceof Error) {
                $this->handleResponse($message);
            } elseif ($message instanceof Request) {
                $this->handleRequest($message);
            } elseif ($message instanceof Notification) {
                $this->handleNotification($message);
            }
        }
    }

    private function handleResponse(Response|Error $response): void
    {
        $requestId = $response->getId();

        $this->logger->debug('Handling response', ['id' => $requestId]);

        $this->state->storeResponse($requestId, $response->jsonSerialize());
    }

    private function handleRequest(Request $request): void
    {
        $method = $request::getMethod();

        $this->logger->debug('Received server request', [
            'method' => $method,
            'id' => $request->getId(),
        ]);

        foreach ($this->requestHandlers as $handler) {
            if ($handler->supports($request)) {
                try {
                    $response = $handler->handle($request);
                } catch (\Throwable $e) {
                    $this->logger->error('Unexpected error while handling request', [
                        'method' => $method,
                        'exception' => $e,
                    ]);

                    $response = Error::forInternalError(
                        \sprintf('Unexpected error while handling "%s" request', $method),
                        $request->getId()
                    );
                }

                $this->sendResponse($response);

                return;
            }
        }

        $error = Error::forMethodNotFound(
            \sprintf('Client does not handle "%s" requests.', $method),
            $request->getId()
        );

        $this->sendResponse($error);
    }

    private function handleNotification(Notification $notification): void
    {
        $method = $notification::getMethod();

        $this->logger->debug('Received server notification', [
            'method' => $method,
        ]);

        foreach ($this->notificationHandlers as $handler) {
            if ($handler->supports($notification)) {
                try {
                    $handler->handle($notification);
                } catch (\Throwable $e) {
                    $this->logger->warning('Notification handler failed', ['exception' => $e]);
                }

                return;
            }
        }
    }

    public function getState(): ClientStateInterface
    {
        return $this->state;
    }
}
