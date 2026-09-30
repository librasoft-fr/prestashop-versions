<?php

declare(strict_types=1);

namespace Sentry\Metrics\Types;

use Sentry\Tracing\SpanId;
use Sentry\Tracing\TraceId;
use Sentry\Unit;


final class CounterMetric extends Metric
{
    
    public const TYPE = 'counter';

    
    private $value;

    
    public function __construct(
        string $name,
        $value,
        TraceId $traceId,
        SpanId $spanId,
        array $attributes,
        float $timestamp,
        ?Unit $unit
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
