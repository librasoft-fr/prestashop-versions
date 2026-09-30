<?php

namespace PsMcpServerDeps\Mcp\Client\State;

use PsMcpServerDeps\Mcp\Schema\Implementation;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;

interface ClientStateInterface
{
    public function nextRequestId(): int;

    public function addPendingRequest(int|string $requestId, int $timeout): void;

    public function removePendingRequest(int|string $requestId): void;

    public function getPendingRequests(): array;

    public function storeResponse(int|string $requestId, array $responseData): void;

    public function consumeResponse(int|string $requestId): Response|Error|null;

    public function setInitialized(bool $initialized): void;

    public function isInitialized(): bool;

    public function setServerInfo(Implementation $serverInfo): void;

    public function getServerInfo(): ?Implementation;

    public function setInstructions(?string $instructions): void;

    public function getInstructions(): ?string;

    public function storeProgress(string $token, float $progress, ?float $total, ?string $message): void;

    public function consumeProgressUpdates(): array;
}
