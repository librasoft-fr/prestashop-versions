<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Integration;

use PsMcpServerDeps\Sentry\ErrorHandler;
use PsMcpServerDeps\Sentry\Exception\SilencedErrorException;
use PsMcpServerDeps\Sentry\Options;
use PsMcpServerDeps\Sentry\SentrySdk;

final class ErrorListenerIntegration extends AbstractErrorListenerIntegration implements OptionAwareIntegrationInterface
{
    private $options;

    public function setOptions(Options $options): void
    {
        $this->options = $options;
    }

    public function setupOnce(): void
    {
        ErrorHandler::registerOnceErrorHandler($this->options)
                    ->addErrorHandlerListener(
                        static function (\ErrorException $exception): void {
                            $currentHub = SentrySdk::getCurrentHub();
                            $integration = $currentHub->getIntegration(self::class);
                            $client = $currentHub->getClient();

                            if ($integration === null || $client === null) {
                                return;
                            }

                            if ($exception instanceof SilencedErrorException && !$client->getOptions()->shouldCaptureSilencedErrors()) {
                                return;
                            }

                            if (!$exception instanceof SilencedErrorException && !($client->getOptions()->getErrorTypes() & $exception->getSeverity())) {
                                return;
                            }

                            $integration->captureException($currentHub, $exception);
                        }
                    );
    }

    public static function make(Options $options): self
    {
        $integration = new self();

        $integration->setOptions($options);

        return $integration;
    }
}
