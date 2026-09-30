<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Integration;

use PsMcpServerDeps\Sentry\Event;
use PsMcpServerDeps\Sentry\EventHint;
use PsMcpServerDeps\Sentry\SentrySdk;
use PsMcpServerDeps\Sentry\State\Scope;

final class TransactionIntegration implements IntegrationInterface
{
    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(static function (Event $event, EventHint $hint): Event {
            $integration = SentrySdk::getCurrentHub()->getIntegration(self::class);

            if ($integration === null) {
                return $event;
            }

            if ($event->getTransaction() !== null) {
                return $event;
            }

            if (isset($hint->extra['transaction']) && \is_string($hint->extra['transaction'])) {
                $event->setTransaction($hint->extra['transaction']);
            } elseif (isset($_SERVER['PATH_INFO'])) {
                $event->setTransaction($_SERVER['PATH_INFO']);
            }

            return $event;
        });
    }
}
