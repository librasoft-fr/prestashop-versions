<?php

declare(strict_types=1);

namespace Sentry\Integration;

use Sentry\ErrorHandler;
use Sentry\SentrySdk;


final class ExceptionListenerIntegration extends AbstractErrorListenerIntegration
{
    
    public function setupOnce(): void
    {
        $errorHandler = ErrorHandler::registerOnceExceptionHandler();
        $errorHandler->addExceptionHandlerListener(static function (\Throwable $exception): void {
            $currentHub = SentrySdk::getCurrentHub();
            $integration = $currentHub->getIntegration(self::class);

            
            
            if ($integration === null) {
                return;
            }

            $integration->captureException($currentHub, $exception);
        });
    }
}
