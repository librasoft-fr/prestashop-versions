<?php

namespace PsMcpServerDeps\Mcp\Server\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class InMemoryTransport extends BaseTransport
{
    public function __construct(
        private readonly array $messages = [],
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger);
    }

    public function onMessage(callable $listener): void
    {
        $this->messageListener = $listener;
    }

    public function send(string $data, array $context): void
    {
        if (isset($context['session_id'])) {
            $this->sessionId = $context['session_id'];
        }
    }

    public function listen(): mixed
    {
        $this->logger->info('InMemoryTransport is processing messages...');

        foreach ($this->messages as $message) {
            $this->handleMessage($message, $this->sessionId);
        }

        $this->logger->info('InMemoryTransport finished processing.');
        $this->handleSessionEnd($this->sessionId);

        $this->sessionId = null;

        return null;
    }

    public function setSessionId(?Uuid $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function close(): void
    {
        $this->handleSessionEnd($this->sessionId);
        $this->sessionId = null;
    }
}
