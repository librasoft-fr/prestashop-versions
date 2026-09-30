<?php



namespace Mcp\Server\Handler\Notification;

use Mcp\Schema\JsonRpc\Notification;
use Mcp\Schema\Notification\InitializedNotification;
use Mcp\Server\Session\SessionInterface;


final class InitializedHandler implements NotificationHandlerInterface
{
    public function supports(Notification $notification): bool
    {
        return $notification instanceof InitializedNotification;
    }

    public function handle(Notification $notification, SessionInterface $session): void
    {
        \assert($notification instanceof InitializedNotification);

        $session->set('initialized', true);
    }
}
