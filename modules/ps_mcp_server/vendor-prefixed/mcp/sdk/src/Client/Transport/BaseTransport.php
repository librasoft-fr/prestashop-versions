<?php

namespace PsMcpServerDeps\Mcp\Client\Transport;

use PsMcpServerDeps\Mcp\Client\State\ClientStateInterface;
use PsMcpServerDeps\Mcp\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class BaseTransport implements TransportInterface
{
    protected $initializeCallback;

    protected $messageCallback;

    protected $errorCallback;

    protected $closeCallback;

    protected ?ClientStateInterface $state = null;
    protected LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function onInitialize(callable $listener): void
    {
        $this->initializeCallback = $listener;
    }

    public function onMessage(callable $listener): void
    {
        $this->messageCallback = $listener;
    }

    public function onError(callable $listener): void
    {
        $this->errorCallback = $listener;
    }

    public function onClose(callable $listener): void
    {
        $this->closeCallback = $listener;
    }

    public function setState(ClientStateInterface $state): void
    {
        $this->state = $state;
    }

    protected function handleInitialize(): mixed
    {
        if (!\is_callable($this->initializeCallback)) {
            throw new RuntimeException('No initialize listener registered');
        }

        return ($this->initializeCallback)();
    }

    protected function handleMessage(string $message): void
    {
        if (\is_callable($this->messageCallback)) {
            try {
                ($this->messageCallback)($message);
            } catch (\Throwable $e) {
                $this->handleError($e);
            }
        }
    }

    protected function handleError(\Throwable $error): void
    {
        $this->logger->error('Transport error', ['exception' => $error]);

        if (\is_callable($this->errorCallback)) {
            ($this->errorCallback)($error);
        }
    }

    protected function handleClose(string $reason): void
    {
        $this->logger->info('Transport closed', ['reason' => $reason]);

        if (\is_callable($this->closeCallback)) {
            ($this->closeCallback)($reason);
        }
    }
}
