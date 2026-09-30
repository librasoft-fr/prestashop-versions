<?php

namespace PsMcpServerDeps\Mcp\Client\Transport;

use PsMcpServerDeps\Mcp\Client\State\ClientStateInterface;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;

interface TransportInterface
{
    public function connect(): void;

    public function send(string $data): void;

    public function runRequest(\Fiber $fiber, ?callable $onProgress = null): Response|Error;

    public function close(): void;

    public function onInitialize(callable $callback): void;

    public function onMessage(callable $callback): void;

    public function onError(callable $callback): void;

    public function onClose(callable $callback): void;

    public function setState(ClientStateInterface $state): void;
}
