<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Subscriber;

use PrestaShop\Module\PsAskAi\Service\SentryReporter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Report uncaught exceptions from this module's controllers to Sentry.
 *
 * Covers the JSON endpoints (history, conversations, models, upload, …):
 * their errors bubble up to the kernel instead of going through the SSE
 * stream's catch-all in AskAiController::buildStreamResponse. Exceptions
 * from other modules / core controllers are ignored.
 */
class SentryExceptionSubscriber implements EventSubscriberInterface
{
    private const MODULE_NAMESPACE = 'PrestaShop\\Module\\PsAskAi\\';

    public function __construct(private readonly SentryReporter $sentryReporter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!self::isModuleController($request->attributes->get('_controller'))) {
            return;
        }

        $this->sentryReporter->captureException(
            $event->getThrowable(),
            'controller_error',
            [
                'route' => (string) $request->attributes->get('_route'),
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod(),
            ],
            ['route' => (string) $request->attributes->get('_route')],
        );
    }

    public static function isModuleController(mixed $controller): bool
    {
        if (is_string($controller)) {
            return str_contains($controller, self::MODULE_NAMESPACE);
        }

        if (is_array($controller) && isset($controller[0]) && is_object($controller[0])) {
            return str_starts_with(get_class($controller[0]), self::MODULE_NAMESPACE);
        }

        if (is_object($controller) && !$controller instanceof \Closure) {
            return str_starts_with(get_class($controller), self::MODULE_NAMESPACE);
        }

        return false;
    }
}
