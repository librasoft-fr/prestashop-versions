<?php



namespace Mcp\Client\Handler\Notification;

use Mcp\Client\State\ClientStateInterface;
use Mcp\Schema\JsonRpc\Notification;
use Mcp\Schema\Notification\ProgressNotification;


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
