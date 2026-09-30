<?php

namespace PsMcpServerDeps\Mcp\Schema\Request;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;

final class ReadResourceRequest extends Request
{
    public function __construct(
        public readonly string $uri,
    ) {
    }

    public static function getMethod(): string
    {
        return 'resources/read';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['uri']) || !\is_string($params['uri']) || empty($params['uri'])) {
            throw new InvalidArgumentException('Missing or invalid "uri" parameter for resources/read.');
        }

        return new self($params['uri']);
    }

    protected function getParams(): array
    {
        return [
            'uri' => $this->uri,
        ];
    }
}
