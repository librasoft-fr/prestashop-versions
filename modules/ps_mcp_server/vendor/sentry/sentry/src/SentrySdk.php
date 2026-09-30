<?php

declare(strict_types=1);

namespace Sentry;

use Sentry\Logs\Logs;
use Sentry\Metrics\TraceMetrics;
use Sentry\State\Hub;
use Sentry\State\HubInterface;
use Sentry\State\RuntimeContext;
use Sentry\State\RuntimeContextManager;


final class SentrySdk
{
    
    private static $currentHub;

    
    private static $runtimeContextManager;

    
    private function __construct()
    {
    }

    
    public static function init(): HubInterface
    {
        self::$currentHub = new Hub();
        self::$runtimeContextManager = new RuntimeContextManager(self::$currentHub);

        return self::getCurrentHub();
    }

    
    public static function getCurrentHub(): HubInterface
    {
        return self::getRuntimeContextManager()->getCurrentHub();
    }

    
    public static function setCurrentHub(HubInterface $hub): HubInterface
    {
        $wasSetOnActiveRuntimeContext = self::getRuntimeContextManager()->setCurrentHub($hub);

        if (!$wasSetOnActiveRuntimeContext) {
            self::$currentHub = $hub;
        }

        return $hub;
    }

    public static function startContext(): void
    {
        self::getRuntimeContextManager()->startContext();
    }

    public static function endContext(?int $timeout = null): void
    {
        self::getRuntimeContextManager()->endContext($timeout);
    }

    
    public static function withContext(callable $callback, ?int $timeout = null)
    {
        $runtimeContextManager = self::getRuntimeContextManager();
        $startedNewContext = !$runtimeContextManager->hasActiveContext();

        if ($startedNewContext) {
            $runtimeContextManager->startContext();
        }

        try {
            return $callback();
        } finally {
            if ($startedNewContext) {
                $runtimeContextManager->endContext($timeout);
            }
        }
    }

    
    public static function getCurrentRuntimeContext(): RuntimeContext
    {
        return self::getRuntimeContextManager()->getCurrentContext();
    }

    
    public static function flush(): void
    {
        Logs::getInstance()->flush();
        TraceMetrics::getInstance()->flush();

        $client = self::getCurrentHub()->getClient();

        if ($client !== null) {
            $client->flush();
        }
    }

    private static function getRuntimeContextManager(): RuntimeContextManager
    {
        if (self::$currentHub === null) {
            self::$currentHub = new Hub();
        }

        if (self::$runtimeContextManager === null) {
            self::$runtimeContextManager = new RuntimeContextManager(self::$currentHub);
        }

        return self::$runtimeContextManager;
    }
}
