<?php

namespace PsMcpServerDeps\Mcp\Server\Resource;

use PsMcpServerDeps\Mcp\Server\Protocol;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

interface SubscriptionManagerInterface
{
    public function subscribe(SessionInterface $session, string $uri): void;

    public function unsubscribe(SessionInterface $session, string $uri): void;

    public function isSubscribed(SessionInterface $session, string $uri): bool;

    public function notifyResourceChanged(Protocol $protocol, SessionInterface $session, string $uri): void;
}
