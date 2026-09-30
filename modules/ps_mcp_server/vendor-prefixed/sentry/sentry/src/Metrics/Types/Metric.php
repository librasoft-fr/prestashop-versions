<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Metrics\Types;

use PsMcpServerDeps\Sentry\Attributes\AttributeBag;
use PsMcpServerDeps\Sentry\Tracing\SpanId;
use PsMcpServerDeps\Sentry\Tracing\TraceId;
use PsMcpServerDeps\Sentry\Unit;

abstract class Metric
{
    private $name;

    private $traceId;

    private $spanId;

    private $timestamp;

    private $attributes;

    private $unit;

    public function __construct(
        string $name,
        TraceId $traceId,
        SpanId $spanId,
        float $timestamp,
        array $attributes,
        ?Unit $unit,
    ) {
        $this->name = $name;
        $this->unit = $unit;
        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->timestamp = $timestamp;
        $this->attributes = new AttributeBag();

        foreach ($attributes as $key => $value) {
            $this->attributes->set($key, $value);
        }
    }

    abstract public function setValue($value): void;

    abstract public function getType(): string;

    abstract public function getValue();

    public function getName(): string
    {
        return $this->name;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function getTraceId(): TraceId
    {
        return $this->traceId;
    }

    public function getSpanId(): SpanId
    {
        return $this->spanId;
    }

    public function getAttributes(): AttributeBag
    {
        return $this->attributes;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }
}
