<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Integration;

use PsMcpServerDeps\Sentry\ErrorHandler;
use PsMcpServerDeps\Sentry\Exception\FatalErrorException;
use PsMcpServerDeps\Sentry\SentrySdk;

final class FatalErrorListenerIntegration extends AbstractErrorListenerIntegration
{
    public function setupOnce(): void
    {
        $errorHandler = ErrorHandler::registerOnceFatalErrorHandler();
        $errorHandler->addFatalErrorHandlerListener(static function (FatalErrorException $exception): void {
            $currentHub = SentrySdk::getCurrentHub();
            $integration = $currentHub->getIntegration(self::class);
            $client = $currentHub->getClient();

            if ($integration === null || $client === null) {
                return;
            }

            if (!($client->getOptions()->getErrorTypes() & $exception->getSeverity())) {
                return;
            }

            $integration->captureException($currentHub, $exception);
        });
    }
}
