<?php

declare(strict_types=1);

namespace Sentry\Metrics;

use Sentry\EventId;
use Sentry\Metrics\Types\CounterMetric;
use Sentry\Metrics\Types\DistributionMetric;
use Sentry\Metrics\Types\GaugeMetric;
use Sentry\SentrySdk;
use Sentry\Unit;

class TraceMetrics
{
    
    private static $instance;

    public function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new TraceMetrics();
        }

        return self::$instance;
    }

    
    public function count(
        string $name,
        $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        $this->aggregator()->add(
            CounterMetric::TYPE,
            $name,
            $value,
            $attributes,
            $unit
        );
    }

    
    public function distribution(
        string $name,
        $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        $this->aggregator()->add(
            DistributionMetric::TYPE,
            $name,
            $value,
            $attributes,
            $unit
        );
    }

    
    public function gauge(
        string $name,
        $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        $this->aggregator()->add(
            GaugeMetric::TYPE,
            $name,
            $value,
            $attributes,
            $unit
        );
    }

    public function flush(): ?EventId
    {
        return $this->aggregator()->flush();
    }

    private function aggregator(): MetricsAggregator
    {
        return SentrySdk::getCurrentRuntimeContext()->getMetricsAggregator();
    }
}
