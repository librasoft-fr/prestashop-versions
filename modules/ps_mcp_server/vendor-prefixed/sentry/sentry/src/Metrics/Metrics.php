<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Metrics;

use PsMcpServerDeps\Sentry\EventId;
use PsMcpServerDeps\Sentry\Tracing\SpanContext;
use PsMcpServerDeps\Sentry\Unit;

use function PsMcpServerDeps\Sentry\trace;

class_alias(Unit::class, '\PsMcpServerDeps\Sentry\Metrics\MetricsUnit');

class Metrics
{
    private static $instance;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function increment(
        string $key,
        float $value,
        ?Unit $unit = null,
        array $tags = [],
        ?int $timestamp = null,
        int $stackLevel = 0,
    ): void {
    }

    public function distribution(
        string $key,
        float $value,
        ?Unit $unit = null,
        array $tags = [],
        ?int $timestamp = null,
        int $stackLevel = 0,
    ): void {
    }

    public function gauge(
        string $key,
        float $value,
        ?Unit $unit = null,
        array $tags = [],
        ?int $timestamp = null,
        int $stackLevel = 0,
    ): void {
    }

    public function set(
        string $key,
        $value,
        ?Unit $unit = null,
        array $tags = [],
        ?int $timestamp = null,
        int $stackLevel = 0,
    ): void {
    }

    public function timing(
        string $key,
        callable $callback,
        array $tags = [],
        int $stackLevel = 0,
    ) {
        return trace(
            static function () use ($callback) {
                return $callback();
            },
            SpanContext::make()
                ->setOp('metric.timing')
                ->setOrigin('auto.measure.metrics.timing')
                ->setDescription($key)
        );
    }

    public function flush(): ?EventId
    {
        return null;
    }
}
