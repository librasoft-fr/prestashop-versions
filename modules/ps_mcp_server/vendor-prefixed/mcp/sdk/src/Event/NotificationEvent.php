<?php

namespace PsMcpServerDeps\Mcp\Event;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class NotificationEvent
{
    public function __construct(
        private Notification $notification,
        private readonly SessionInterface $session,
    ) {
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getMethod(): string
    {
        return $this->notification::getMethod();
    }
}
