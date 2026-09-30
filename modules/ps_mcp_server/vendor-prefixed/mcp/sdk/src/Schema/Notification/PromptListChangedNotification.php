<?php

namespace PsMcpServerDeps\Mcp\Schema\Notification;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;

class PromptListChangedNotification extends Notification
{
    public static function getMethod(): string
    {
        return 'notifications/prompts/list_changed';
    }

    protected static function fromParams(?array $params): Notification
    {
        return new self();
    }

    protected function getParams(): ?array
    {
        return null;
    }
}
