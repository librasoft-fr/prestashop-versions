<?php



namespace Mcp\Client\State;

use Mcp\Schema\Implementation;
use Mcp\Schema\JsonRpc\Error;
use Mcp\Schema\JsonRpc\Response;


class ClientState implements ClientStateInterface
{
    private int $requestIdCounter = 1;
    private bool $initialized = false;
    private ?Implementation $serverInfo = null;
    private ?string $instructions = null;

    
    private array $pendingRequests = [];

    
    private array $responses = [];

    
    private array $progressUpdates = [];

    public function nextRequestId(): int
    {
        return $this->requestIdCounter++;
    }

    public function addPendingRequest(int|string $requestId, int $timeout): void
    {
        $this->pendingRequests[$requestId] = [
            'request_id' => $requestId,
            'timestamp' => time(),
            'timeout' => $timeout,
        ];
    }

    public function removePendingRequest(int|string $requestId): void
    {
        unset($this->pendingRequests[$requestId]);
    }

    public function getPendingRequests(): array
    {
        return $this->pendingRequests;
    }

    public function storeResponse(int|string $requestId, array $responseData): void
    {
        $this->responses[$requestId] = $responseData;
    }

    public function consumeResponse(int|string $requestId): Response|Error|null
    {
        if (!isset($this->responses[$requestId])) {
            return null;
        }

        $data = $this->responses[$requestId];
        unset($this->responses[$requestId]);
        $this->removePendingRequest($requestId);

        if (isset($data['error'])) {
            return Error::fromArray($data);
        }

        return Response::fromArray($data);
    }

    public function setInitialized(bool $initialized): void
    {
        $this->initialized = $initialized;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function setServerInfo(Implementation $serverInfo): void
    {
        $this->serverInfo = $serverInfo;
    }

    public function getServerInfo(): ?Implementation
    {
        return $this->serverInfo;
    }

    public function setInstructions(?string $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function storeProgress(string $token, float $progress, ?float $total, ?string $message): void
    {
        $this->progressUpdates[] = [
            'token' => $token,
            'progress' => $progress,
            'total' => $total,
            'message' => $message,
        ];
    }

    public function consumeProgressUpdates(): array
    {
        $updates = $this->progressUpdates;
        $this->progressUpdates = [];

        return $updates;
    }
}
