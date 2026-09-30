<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Notification;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

interface NotificationHandlerInterface
{
    public function supports(Notification $notification): bool;

    public function handle(Notification $notification, SessionInterface $session): void;
}
