<?php

namespace PsMcpServerDeps\Mcp\Server;

use PsMcpServerDeps\Mcp\Event\ErrorEvent;
use PsMcpServerDeps\Mcp\Event\NotificationEvent;
use PsMcpServerDeps\Mcp\Event\RequestEvent;
use PsMcpServerDeps\Mcp\Event\ResponseEvent;
use PsMcpServerDeps\Mcp\Exception\InvalidInputMessageException;
use PsMcpServerDeps\Mcp\JsonRpc\MessageFactory;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\InitializeRequest;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;
use PsMcpServerDeps\Mcp\Server\Session\SessionManagerInterface;
use PsMcpServerDeps\Mcp\Server\Transport\TransportInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

class Protocol
{
    private const SESSION_REQUEST_ID_COUNTER = '_mcp.request_id_counter';

    private const SESSION_PENDING_REQUESTS = '_mcp.pending_requests';

    private const SESSION_RESPONSES = '_mcp.responses';

    private const SESSION_OUTGOING_QUEUE = '_mcp.outgoing_queue';

    public const SESSION_ACTIVE_REQUEST_META = '_mcp.active_request_meta';

    public const SESSION_LOGGING_LEVEL = '_mcp.logging_level';

    public function __construct(
        private readonly array $requestHandlers,
        private readonly array $notificationHandlers,
        private readonly MessageFactory $messageFactory,
        private readonly SessionManagerInterface $sessionManager,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function connect(TransportInterface $transport): void
    {
        $transport->onMessage($this->processInput(...));

        $transport->onSessionEnd($this->destroySession(...));

        $transport->setOutgoingMessagesProvider($this->consumeOutgoingMessages(...));

        $transport->setPendingRequestsProvider($this->getPendingRequests(...));

        $transport->setResponseFinder($this->checkResponse(...));

        $transport->setFiberYieldHandler($this->handleFiberYield(...));

        $this->logger->info('Protocol connected to transport', ['transport' => $transport::class]);
    }

    public function processInput(TransportInterface $transport, string $input, ?Uuid $sessionId): void
    {
        $this->logger->info('Received message to process.', ['message' => $input]);

        $this->sessionManager->gc();

        try {
            $messages = $this->messageFactory->create($input);
        } catch (\JsonException $e) {
            $this->logger->warning('Failed to decode json message.', ['exception' => $e]);
            $error = Error::forParseError($e->getMessage());
            $this->sendResponse($transport, $error, null);

            return;
        }

        $session = $this->resolveSession($transport, $sessionId, $messages);
        if (null === $session) {
            return;
        }

        foreach ($messages as $message) {
            if ($message instanceof InvalidInputMessageException) {
                $this->handleInvalidMessage($transport, $message, $session);
            } elseif ($message instanceof Request) {
                $this->handleRequest($transport, $message, $session);
            } elseif ($message instanceof Response || $message instanceof Error) {
                $this->handleResponse($message, $session);
            } elseif ($message instanceof Notification) {
                $this->handleNotification($message, $session);
            }
        }

        $session->save();
    }

    private function handleInvalidMessage(TransportInterface $transport, InvalidInputMessageException $exception, SessionInterface $session): void
    {
        $this->logger->warning('Failed to create message.', ['exception' => $exception]);

        $error = Error::forInvalidRequest($exception->getMessage());
        $this->sendResponse($transport, $error, $session);
    }

    private function dispatchEvent(object $event): object
    {
        return $this->eventDispatcher?->dispatch($event) ?? $event;
    }

    private function handleRequest(TransportInterface $transport, Request $request, SessionInterface $session): void
    {
        $this->logger->info('Handling request.', ['request' => $request]);

        $session->set(self::SESSION_ACTIVE_REQUEST_META, $request->getMeta());

        $event = $this->dispatchEvent(new RequestEvent($request, $session));
        $request = $event->getRequest();

        $handlerFound = false;

        foreach ($this->requestHandlers as $handler) {
            if (!$handler->supports($request)) {
                continue;
            }

            $handlerFound = true;

            try {
                $fiber = new \Fiber(static fn () => $handler->handle($request, $session));

                $result = $fiber->start();

                if ($fiber->isSuspended()) {
                    if (\is_array($result) && isset($result['type'])) {
                        if ('notification' === $result['type']) {
                            $notification = $result['notification'];
                            $this->sendNotification($notification, $session);
                        } elseif ('request' === $result['type']) {
                            $request = $result['request'];
                            $timeout = $result['timeout'] ?? 120;
                            $this->sendRequest($request, $timeout, $session);
                        }
                    }

                    $transport->attachFiberToSession($fiber, $session->getId());

                    return;
                }
                $finalResult = $fiber->getReturn();

                if ($finalResult instanceof Response) {
                    $responseEvent = $this->dispatchEvent(new ResponseEvent($finalResult, $request, $session));
                    $finalResult = $responseEvent->getResponse();
                } elseif ($finalResult instanceof Error) {
                    $errorEvent = $this->dispatchEvent(new ErrorEvent($finalResult, $request, $session, null));
                    $finalResult = $errorEvent->getError();
                }

                $this->sendResponse($transport, $finalResult, $session);
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning(\sprintf('Invalid argument: %s', $e->getMessage()), ['exception' => $e]);

                $error = Error::forInvalidParams($e->getMessage(), $request->getId());
                $errorEvent = $this->dispatchEvent(new ErrorEvent($error, $request, $session, $e));
                $error = $errorEvent->getError();

                $this->sendResponse($transport, $error, $session);
            } catch (\Throwable $e) {
                $this->logger->error(\sprintf('Uncaught exception: %s', $e->getMessage()), ['exception' => $e]);

                $error = Error::forInternalError($e->getMessage(), $request->getId());
                $errorEvent = $this->dispatchEvent(new ErrorEvent($error, $request, $session, $e));
                $error = $errorEvent->getError();

                $this->sendResponse($transport, $error, $session);
            }

            break;
        }

        if (!$handlerFound) {
            $error = Error::forMethodNotFound(\sprintf('No handler found for method "%s".', $request::getMethod()), $request->getId());
            $errorEvent = $this->dispatchEvent(new ErrorEvent($error, $request, $session, null));
            $error = $errorEvent->getError();

            $this->sendResponse($transport, $error, $session);
        }
    }

    private function handleResponse(Response|Error $response, SessionInterface $session): void
    {
        $this->logger->info('Handling response from client.', ['response' => $response]);

        $messageId = $response->getId();

        $session->set(self::SESSION_RESPONSES . ".{$messageId}", $response->jsonSerialize());
        $session->forget(self::SESSION_ACTIVE_REQUEST_META);

        $this->logger->info('Client response stored in session', [
            'message_id' => $messageId,
        ]);
    }

    private function handleNotification(Notification $notification, SessionInterface $session): void
    {
        $this->logger->info('Handling notification.', ['notification' => $notification]);

        $event = $this->dispatchEvent(new NotificationEvent($notification, $session));
        $notification = $event->getNotification();

        foreach ($this->notificationHandlers as $handler) {
            if (!$handler->supports($notification)) {
                continue;
            }

            try {
                $handler->handle($notification, $session);
            } catch (\Throwable $e) {
                $this->logger->error(\sprintf('Error while handling notification: %s', $e->getMessage()), ['exception' => $e]);
            }
        }
    }

    public function sendRequest(Request $request, int $timeout, SessionInterface $session): int
    {
        $counter = $session->get(self::SESSION_REQUEST_ID_COUNTER, 1000);
        $requestId = $counter++;
        $session->set(self::SESSION_REQUEST_ID_COUNTER, $counter);

        $requestWithId = $request->withId($requestId);

        $this->logger->info('Queueing server request to client', [
            'request_id' => $requestId,
            'method' => $request::getMethod(),
        ]);

        $pending = $session->get(self::SESSION_PENDING_REQUESTS, []);
        $pending[$requestId] = [
            'request_id' => $requestId,
            'timeout' => $timeout,
            'timestamp' => time(),
        ];
        $session->set(self::SESSION_PENDING_REQUESTS, $pending);

        $this->queueOutgoing($requestWithId, ['type' => 'request'], $session);

        return $requestId;
    }

    public function sendNotification(Notification $notification, SessionInterface $session): void
    {
        $this->logger->info('Queueing server notification to client', [
            'method' => $notification::getMethod(),
        ]);

        $this->queueOutgoing($notification, ['type' => 'notification'], $session);
    }

    private function sendResponse(TransportInterface $transport, Response|Error $response, ?SessionInterface $session, array $context = []): void
    {
        if (null === $session) {
            $this->logger->info('Sending immediate response', [
                'response_id' => $response->getId(),
            ]);

            try {
                $encoded = json_encode($response, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->logger->error('Failed to encode response to JSON.', [
                    'message_id' => $response->getId(),
                    'exception' => $e,
                ]);

                $fallbackError = new Error(
                    id: $response->getId(),
                    code: Error::INTERNAL_ERROR,
                    message: 'Response could not be encoded to JSON'
                );

                $encoded = json_encode($fallbackError, \JSON_THROW_ON_ERROR);
            }

            $context['type'] = 'response';
            $transport->send($encoded, $context);
        } else {
            $this->logger->info('Queueing server response', [
                'response_id' => $response->getId(),
            ]);

            $this->queueOutgoing($response, ['type' => 'response'], $session);
        }
    }

    private function queueOutgoing(Request|Notification|Response|Error $message, array $context, SessionInterface $session): void
    {
        try {
            $encoded = json_encode($message, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->error('Failed to encode message to JSON.', [
                'exception' => $e,
            ]);

            return;
        }

        $queue = $session->get(self::SESSION_OUTGOING_QUEUE, []);
        $queue[] = [
            'message' => $encoded,
            'context' => $context,
        ];
        $session->set(self::SESSION_OUTGOING_QUEUE, $queue);
    }

    public function consumeOutgoingMessages(Uuid $sessionId): array
    {
        $session = $this->sessionManager->createWithId($sessionId);
        $queue = $session->get(self::SESSION_OUTGOING_QUEUE, []);
        $session->set(self::SESSION_OUTGOING_QUEUE, []);
        $session->save();

        return $queue;
    }

    public function checkResponse(int $requestId, Uuid $sessionId): Response|Error|null
    {
        $session = $this->sessionManager->createWithId($sessionId);
        $responseData = $session->get(self::SESSION_RESPONSES . ".{$requestId}");

        if (null === $responseData) {
            return null;
        }

        $this->logger->debug('Found and consuming client response.', [
            'request_id' => $requestId,
            'session_id' => $sessionId->toRfc4122(),
        ]);

        $session->set(self::SESSION_RESPONSES . ".{$requestId}", null);
        $pending = $session->get(self::SESSION_PENDING_REQUESTS, []);
        unset($pending[$requestId]);
        $session->set(self::SESSION_PENDING_REQUESTS, $pending);
        $session->save();

        try {
            if (isset($responseData['error'])) {
                return Error::fromArray($responseData);
            }

            return Response::fromArray($responseData);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to reconstruct client response from session.', [
                'request_id' => $requestId,
                'exception' => $e,
                'response_data' => $responseData,
            ]);

            return null;
        }
    }

    public function getPendingRequests(Uuid $sessionId): array
    {
        $session = $this->sessionManager->createWithId($sessionId);

        return $session->get(self::SESSION_PENDING_REQUESTS, []);
    }

    public function handleFiberYield(mixed $yieldedValue, ?Uuid $sessionId): void
    {
        if (!$sessionId) {
            $this->logger->warning('Fiber yielded value without associated session context.');

            return;
        }

        if (!\is_array($yieldedValue) || !isset($yieldedValue['type'])) {
            $this->logger->warning('Fiber yielded unexpected payload.', [
                'payload' => $yieldedValue,
                'session_id' => $sessionId->toRfc4122(),
            ]);

            return;
        }

        $session = $this->sessionManager->createWithId($sessionId);

        $payloadSessionId = $yieldedValue['session_id'] ?? null;
        if (\is_string($payloadSessionId) && $payloadSessionId !== $sessionId->toRfc4122()) {
            $this->logger->warning('Fiber yielded payload with mismatched session ID.', [
                'payload_session_id' => $payloadSessionId,
                'expected_session_id' => $sessionId->toRfc4122(),
            ]);
        }

        try {
            if ('notification' === $yieldedValue['type']) {
                $notification = $yieldedValue['notification'] ?? null;
                if (!$notification instanceof Notification) {
                    $this->logger->warning('Fiber yielded notification without Notification instance.', [
                        'payload' => $yieldedValue,
                    ]);

                    return;
                }

                $this->sendNotification($notification, $session);
            } elseif ('request' === $yieldedValue['type']) {
                $request = $yieldedValue['request'] ?? null;
                if (!$request instanceof Request) {
                    $this->logger->warning('Fiber yielded request without Request instance.', [
                        'payload' => $yieldedValue,
                    ]);

                    return;
                }

                $timeout = isset($yieldedValue['timeout']) ? (int) $yieldedValue['timeout'] : 120;
                $this->sendRequest($request, $timeout, $session);
            } else {
                $this->logger->warning('Fiber yielded unknown operation type.', [
                    'type' => $yieldedValue['type'],
                ]);
            }
        } finally {
            $session->save();
        }
    }

    private function hasInitializeRequest(array $messages): bool
    {
        foreach ($messages as $message) {
            if ($message instanceof InitializeRequest) {
                return true;
            }
        }

        return false;
    }

    private function resolveSession(TransportInterface $transport, ?Uuid $sessionId, array $messages): ?SessionInterface
    {
        if ($this->hasInitializeRequest($messages)) {
            if (\count($messages) > 1) {
                $error = Error::forInvalidRequest('The "initialize" request MUST NOT be part of a batch.');
                $this->sendResponse($transport, $error, null);

                return null;
            }

            if ($sessionId) {
                $error = Error::forInvalidRequest('A session ID MUST NOT be sent with an "initialize" request.');
                $this->sendResponse($transport, $error, null);

                return null;
            }

            $session = $this->sessionManager->create();
            $this->logger->debug('Created new session for initialize', [
                'session_id' => $session->getId()->toRfc4122(),
            ]);

            $transport->setSessionId($session->getId());

            return $session;
        }

        if (!$sessionId) {
            $error = Error::forInvalidRequest('A valid session id is REQUIRED for non-initialize requests.');
            $this->sendResponse($transport, $error, null, ['status_code' => 400]);

            return null;
        }

        if (!$this->sessionManager->exists($sessionId)) {
            $error = Error::forInvalidRequest('Session not found or has expired.');
            $this->sendResponse($transport, $error, null, ['status_code' => 404]);

            return null;
        }

        return $this->sessionManager->createWithId($sessionId);
    }

    public function destroySession(Uuid $sessionId): void
    {
        $this->sessionManager->destroy($sessionId);
        $this->logger->info('Session destroyed.', ['session_id' => $sessionId->toRfc4122()]);
    }
}
