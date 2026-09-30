<?php

namespace PsMcpServerDeps\Mcp\Schema\Notification;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;

class ResourceUpdatedNotification extends Notification
{
    public function __construct(
        public readonly string $uri,
    ) {
    }

    public static function getMethod(): string
    {
        return 'notifications/resources/updated';
    }

    protected static function fromParams(?array $params): Notification
    {
        if (null === $params || !isset($params['uri']) || !\is_string($params['uri'])) {
            throw new InvalidArgumentException('Invalid or missing "uri" parameter for notifications/resources/updated notification.');
        }

        return new self($params['uri']);
    }

    protected function getParams(): ?array
    {
        return [
            'uri' => $this->uri,
        ];
    }
}
