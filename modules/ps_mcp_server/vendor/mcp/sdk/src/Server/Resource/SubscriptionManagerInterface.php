<?php



namespace Mcp\Server\Resource;

use Mcp\Server\Protocol;
use Mcp\Server\Session\SessionInterface;
use Psr\SimpleCache\InvalidArgumentException;


interface SubscriptionManagerInterface
{
    
    public function subscribe(SessionInterface $session, string $uri): void;

    
    public function unsubscribe(SessionInterface $session, string $uri): void;

    
    public function isSubscribed(SessionInterface $session, string $uri): bool;

    
    public function notifyResourceChanged(Protocol $protocol, SessionInterface $session, string $uri): void;
}
