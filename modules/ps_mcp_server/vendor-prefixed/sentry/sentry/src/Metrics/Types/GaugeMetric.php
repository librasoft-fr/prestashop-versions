<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Metrics\Types;

use PsMcpServerDeps\Sentry\Tracing\SpanId;
use PsMcpServerDeps\Sentry\Tracing\TraceId;
use PsMcpServerDeps\Sentry\Unit;

final class GaugeMetric extends Metric
{
    public const TYPE = 'gauge';

    private $value;

    public function __construct(
        string $name,
        $value,
        TraceId $traceId,
        SpanId $spanId,
        array $attributes,
        float $timestamp,
        ?Unit $unit,
    ) {
        parent::__construct($name, $traceId, $spanId, $timestamp, $attributes, $unit);

        $this->value = $value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
