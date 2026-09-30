<?php

namespace PsMcpServerDeps\Mcp\Server\Resource;

use PsMcpServerDeps\Mcp\Schema\Notification\ResourceUpdatedNotification;
use PsMcpServerDeps\Mcp\Server\Protocol;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\InvalidArgumentException;

final class SessionSubscriptionManager implements SubscriptionManagerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function subscribe(SessionInterface $session, string $uri): void
    {
        $subscriptions = $session->get('resource_subscriptions', []);
        $subscriptions[$uri] = true;
        $session->set('resource_subscriptions', $subscriptions);
        $session->save();
    }

    public function unsubscribe(SessionInterface $session, string $uri): void
    {
        $subscriptions = $session->get('resource_subscriptions', []);
        unset($subscriptions[$uri]);
        $session->set('resource_subscriptions', $subscriptions);
        $session->save();
    }

    public function isSubscribed(SessionInterface $session, string $uri): bool
    {
        $subscriptions = $session->get('resource_subscriptions', []);

        return isset($subscriptions[$uri]);
    }

    public function notifyResourceChanged(Protocol $protocol, SessionInterface $session, string $uri): void
    {
        $activeSession = $this->isSubscribed($session, $uri);
        if (!$activeSession) {
            return;
        }

        try {
            $protocol->sendNotification(
                new ResourceUpdatedNotification($uri),
                $session
            );
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Error sending resource notification to session', [
                'session_id' => $session->getId()->toRfc4122(),
                'uri' => $uri,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
