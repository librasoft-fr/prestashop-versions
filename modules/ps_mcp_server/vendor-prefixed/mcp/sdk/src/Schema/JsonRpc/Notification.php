<?php

namespace PsMcpServerDeps\Mcp\Schema\JsonRpc;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

abstract class Notification implements HasMethodInterface, MessageInterface
{
    protected ?array $meta = null;

    abstract public static function getMethod(): string;

    public static function fromArray(array $data): self
    {
        if (isset($data['id'])) {
            throw new InvalidArgumentException('Notification MUST NOT contain an "id" field.');
        }
        if (!isset($data['method']) || !\is_string($data['method'])) {
            throw new InvalidArgumentException('Invalid or missing "method" for Notification.');
        }
        $params = $data['params'] ?? null;
        if (null !== $params && !\is_array($params)) {
            throw new InvalidArgumentException('"params" for Notification must be an array/object or null.');
        }

        $notification = static::fromParams($params);

        if (isset($data['params']['_meta'])) {
            $notification->meta = $data['params']['_meta'];
        }

        return $notification;
    }

    abstract protected static function fromParams(?array $params): self;

    public function jsonSerialize(): array
    {
        $array = [
            'jsonrpc' => MessageInterface::JSONRPC_VERSION,
            'method' => static::getMethod(),
        ];
        if (null !== $params = $this->getParams()) {
            $array['params'] = $params;
        }

        if (null !== $this->meta && !isset($params['meta'])) {
            $array['params']['_meta'] = $this->meta;
        }

        return $array;
    }

    abstract protected function getParams(): ?array;
}
