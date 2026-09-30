<?php

namespace PsMcpServerDeps\Mcp\Schema\JsonRpc;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class Error implements MessageInterface
{
    public const PARSE_ERROR = -32700;
    public const INVALID_REQUEST = -32600;
    public const METHOD_NOT_FOUND = -32601;
    public const INVALID_PARAMS = -32602;
    public const INTERNAL_ERROR = -32603;
    public const SERVER_ERROR = -32000;
    public const RESOURCE_NOT_FOUND = -32002;

    public function __construct(
        public readonly string|int $id,
        public readonly int $code,
        public readonly string $message,
        public readonly mixed $data = null,
    ) {
    }

    final public static function fromArray(array $data): self
    {
        if (!isset($data['jsonrpc']) || MessageInterface::JSONRPC_VERSION !== $data['jsonrpc']) {
            throw new InvalidArgumentException('Invalid or missing "jsonrpc" in Error data.');
        }
        if (!isset($data['id'])) {
            throw new InvalidArgumentException('Invalid or missing "id" in Error data.');
        }
        if (!\is_string($data['id']) && !\is_int($data['id'])) {
            throw new InvalidArgumentException('Invalid "id" type in Error data.');
        }
        if (!isset($data['error']) || !\is_array($data['error'])) {
            throw new InvalidArgumentException('Invalid or missing "error" field in Error data.');
        }
        if (!isset($data['error']['code']) || !\is_int($data['error']['code'])) {
            throw new InvalidArgumentException('Invalid or missing "code" in Error data.');
        }
        if (!isset($data['error']['message']) || !\is_string($data['error']['message'])) {
            throw new InvalidArgumentException('Invalid or missing "message" in Error data.');
        }

        return new self($data['id'], $data['error']['code'], $data['error']['message'], $data['error']['data'] ?? null);
    }

    final public static function forParseError(string $message, string|int $id = ''): self
    {
        return new self($id, self::PARSE_ERROR, $message);
    }

    final public static function forInvalidRequest(string $message, string|int $id = ''): self
    {
        return new self($id, self::INVALID_REQUEST, $message);
    }

    final public static function forMethodNotFound(string $message, string|int $id = ''): self
    {
        return new self($id, self::METHOD_NOT_FOUND, $message);
    }

    final public static function forInvalidParams(string $message, string|int $id = '', mixed $data = null): self
    {
        return new self($id, self::INVALID_PARAMS, $message, $data);
    }

    final public static function forInternalError(string $message, string|int $id = ''): self
    {
        return new self($id, self::INTERNAL_ERROR, $message);
    }

    final public static function forServerError(string $message, string|int $id = ''): self
    {
        return new self($id, self::SERVER_ERROR, $message);
    }

    final public static function forResourceNotFound(string $message, string|int $id = ''): self
    {
        return new self($id, self::RESOURCE_NOT_FOUND, $message);
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        $error = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        if (null !== $this->data) {
            $error['data'] = $this->data;
        }

        return [
            'jsonrpc' => MessageInterface::JSONRPC_VERSION,
            'id' => $this->id,
            'error' => $error,
        ];
    }
}
