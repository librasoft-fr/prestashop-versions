<?php



namespace Mcp\Client\Handler\Notification;

use Mcp\Schema\JsonRpc\Notification;


interface NotificationHandlerInterface
{
    
    public function supports(Notification $notification): bool;

    
    public function handle(Notification $notification): void;
}
