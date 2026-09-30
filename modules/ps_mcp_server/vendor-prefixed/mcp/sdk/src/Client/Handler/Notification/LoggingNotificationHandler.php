<?php

namespace PsMcpServerDeps\Mcp\Client\Handler\Notification;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;
use PsMcpServerDeps\Mcp\Schema\Notification\LoggingMessageNotification;

class LoggingNotificationHandler implements NotificationHandlerInterface
{
    public function __construct(
        private readonly mixed $callback,
    ) {
    }

    public function supports(Notification $notification): bool
    {
        return $notification instanceof LoggingMessageNotification;
    }

    public function handle(Notification $notification): void
    {
        \assert($notification instanceof LoggingMessageNotification);

        ($this->callback)($notification);
    }
}
