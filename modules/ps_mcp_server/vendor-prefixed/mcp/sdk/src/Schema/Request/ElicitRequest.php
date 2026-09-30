<?php

namespace PsMcpServerDeps\Mcp\Schema\Request;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\Elicitation\ElicitationSchema;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;

final class ElicitRequest extends Request
{
    public function __construct(
        public readonly string $message,
        public readonly ElicitationSchema $requestedSchema,
    ) {
    }

    public static function getMethod(): string
    {
        return 'elicitation/create';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['message']) || !\is_string($params['message'])) {
            throw new InvalidArgumentException('Missing or invalid "message" parameter for elicitation/create.');
        }

        if (!isset($params['requestedSchema']) || !\is_array($params['requestedSchema'])) {
            throw new InvalidArgumentException('Missing or invalid "requestedSchema" parameter for elicitation/create.');
        }

        return new self(
            $params['message'],
            ElicitationSchema::fromArray($params['requestedSchema']),
        );
    }

    protected function getParams(): array
    {
        return [
            'message' => $this->message,
            'requestedSchema' => $this->requestedSchema,
        ];
    }
}
