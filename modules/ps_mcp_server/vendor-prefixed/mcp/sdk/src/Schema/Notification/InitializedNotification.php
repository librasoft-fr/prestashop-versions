<?php

namespace PsMcpServerDeps\Mcp\Schema\Notification;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;

class InitializedNotification extends Notification
{
    public static function getMethod(): string
    {
        return 'notifications/initialized';
    }

    public static function fromParams(?array $params): self
    {
        return new self();
    }

    protected function getParams(): ?array
    {
        return null;
    }
}
