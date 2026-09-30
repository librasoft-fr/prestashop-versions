<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Integration;

use PsMcpServerDeps\Sentry\Event;
use PsMcpServerDeps\Sentry\ExceptionMechanism;
use PsMcpServerDeps\Sentry\State\HubInterface;
use PsMcpServerDeps\Sentry\State\Scope;

abstract class AbstractErrorListenerIntegration implements IntegrationInterface
{
    protected function captureException(HubInterface $hub, \Throwable $exception): void
    {
        $hub->withScope(function (Scope $scope) use ($hub, $exception): void {
            $scope->addEventProcessor(\Closure::fromCallable([$this, 'addExceptionMechanismToEvent']));

            $hub->captureException($exception);
        });
    }

    protected function addExceptionMechanismToEvent(Event $event): Event
    {
        $exceptions = $event->getExceptions();

        foreach ($exceptions as $exception) {
            $data = [];
            $mechanism = $exception->getMechanism();
            if ($mechanism !== null) {
                $data = $mechanism->getData();
            }

            $exception->setMechanism(new ExceptionMechanism(ExceptionMechanism::TYPE_GENERIC, false, $data));
        }

        return $event;
    }
}
