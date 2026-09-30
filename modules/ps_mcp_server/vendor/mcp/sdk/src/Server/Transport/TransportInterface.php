<?php



namespace Mcp\Server\Transport;

use Mcp\Schema\JsonRpc\Error;
use Mcp\Schema\JsonRpc\Response;
use Symfony\Component\Uid\Uuid;


interface TransportInterface
{
    
    public function initialize(): void;

    
    public function listen(): mixed;

    
    public function send(string $data, array $context): void;

    
    public function close(): void;

    
    public function onMessage(callable $listener): void;

    
    public function onSessionEnd(callable $listener): void;

    
    public function setOutgoingMessagesProvider(callable $provider): void;

    
    public function setPendingRequestsProvider(callable $provider): void;

    
    public function setResponseFinder(callable $finder): void;

    
    public function setFiberYieldHandler(callable $handler): void;

    
    public function attachFiberToSession(\Fiber $fiber, Uuid $sessionId): void;

    
    public function setSessionId(?Uuid $sessionId): void;
}
