<?php



namespace Mcp\Server\Handler\Notification;

use Mcp\Schema\JsonRpc\Notification;
use Mcp\Server\Session\SessionInterface;


interface NotificationHandlerInterface
{
    public function supports(Notification $notification): bool;

    public function handle(Notification $notification, SessionInterface $session): void;
}
