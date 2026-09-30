<?php

namespace PsMcpServerDeps\Mcp\Client\Handler\Notification;

use PsMcpServerDeps\Mcp\Client\State\ClientStateInterface;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;
use PsMcpServerDeps\Mcp\Schema\Notification\ProgressNotification;

class ProgressNotificationHandler implements NotificationHandlerInterface
{
    public function __construct(
        private readonly ClientStateInterface $state,
    ) {
    }

    public function supports(Notification $notification): bool
    {
        return $notification instanceof ProgressNotification;
    }

    public function handle(Notification $notification): void
    {
        if (!$notification instanceof ProgressNotification) {
            return;
        }

        $this->state->storeProgress(
            (string) $notification->progressToken,
            $notification->progress,
            $notification->total,
            $notification->message,
        );
    }
}
