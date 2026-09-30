<?php

namespace PsMcpServerDeps\Mcp\Schema\JsonRpc;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class Response implements MessageInterface
{
    public function __construct(
        public readonly string|int $id,

        public readonly mixed $result,
    ) {
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    public static function fromArray(array $data): self
    {
        if (($data['jsonrpc'] ?? null) !== MessageInterface::JSONRPC_VERSION) {
            throw new InvalidArgumentException('Invalid or missing "jsonrpc" version for Response.');
        }
        if (!isset($data['id'])) {
            throw new InvalidArgumentException('Missing "id" for Response.');
        }
        if (!\is_string($data['id']) && !\is_int($data['id'])) {
            throw new InvalidArgumentException('Invalid "id" type for Response.');
        }
        if (!isset($data['result'])) {
            throw new InvalidArgumentException('Response must contain "result" field.');
        }
        if (!\is_array($data['result'])) {
            throw new InvalidArgumentException('Response "result" must be an array.');
        }

        return new self($data['id'], $data['result']);
    }

    public function jsonSerialize(): array
    {
        return [
            'jsonrpc' => MessageInterface::JSONRPC_VERSION,
            'id' => $this->id,
            'result' => $this->result,
        ];
    }
}
