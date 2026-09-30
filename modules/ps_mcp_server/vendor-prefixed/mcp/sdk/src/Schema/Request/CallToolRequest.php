<?php

namespace PsMcpServerDeps\Mcp\Schema\Request;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;

final class CallToolRequest extends Request
{
    public function __construct(
        public readonly string $name,
        public readonly array $arguments,
    ) {
    }

    public static function getMethod(): string
    {
        return 'tools/call';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['name']) || !\is_string($params['name'])) {
            throw new InvalidArgumentException('Missing or invalid "name" parameter for tools/call.');
        }

        $arguments = $params['arguments'] ?? [];

        if ($arguments instanceof \stdClass) {
            $arguments = (array) $arguments;
        }

        if (!\is_array($arguments)) {
            throw new InvalidArgumentException('Parameter "arguments" must be an array.');
        }

        return new self(
            $params['name'],
            $arguments,
        );
    }

    protected function getParams(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments ?: new \stdClass(),
        ];
    }
}
