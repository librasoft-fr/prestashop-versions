<?php

declare(strict_types=1);

namespace Sentry\Tracing;

class SpanContext
{
    
    private $description;

    
    private $op;

    
    private $status;

    
    protected $parentSpanId;

    
    private $sampled;

    
    private $spanId;

    
    protected $traceId;

    
    private $tags = [];

    
    private $data = [];

    
    private $startTimestamp;

    
    private $endTimestamp;

    
    private $origin;

    
    public static function make()
    {
        return new self();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getOp(): ?string
    {
        return $this->op;
    }

    
    public function setOp(?string $op)
    {
        $this->op = $op;

        return $this;
    }

    public function getStatus(): ?SpanStatus
    {
        return $this->status;
    }

    
    public function setStatus(?SpanStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getParentSpanId(): ?SpanId
    {
        return $this->parentSpanId;
    }

    
    public function setParentSpanId(?SpanId $parentSpanId)
    {
        $this->parentSpanId = $parentSpanId;

        return $this;
    }

    public function getSampled(): ?bool
    {
        return $this->sampled;
    }

    
    public function setSampled(?bool $sampled)
    {
        $this->sampled = $sampled;

        return $this;
    }

    public function getSpanId(): ?SpanId
    {
        return $this->spanId;
    }

    
    public function setSpanId(?SpanId $spanId)
    {
        $this->spanId = $spanId;

        return $this;
    }

    public function getTraceId(): ?TraceId
    {
        return $this->traceId;
    }

    
    public function setTraceId(?TraceId $traceId)
    {
        $this->traceId = $traceId;

        return $this;
    }

    
    public function getTags(): array
    {
        return $this->tags;
    }

    
    public function setTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    
    public function getData(): array
    {
        return $this->data;
    }

    
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function getStartTimestamp(): ?float
    {
        return $this->startTimestamp;
    }

    
    public function setStartTimestamp(?float $startTimestamp)
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    public function getEndTimestamp(): ?float
    {
        return $this->endTimestamp;
    }

    
    public function setEndTimestamp(?float $endTimestamp)
    {
        $this->endTimestamp = $endTimestamp;

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    
    public function setOrigin(?string $origin)
    {
        $this->origin = $origin;

        return $this;
    }
}
