<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

use PsMcpServerDeps\Sentry\EventId;
use PsMcpServerDeps\Sentry\SentrySdk;
use PsMcpServerDeps\Sentry\State\Scope;
use PsMcpServerDeps\Sentry\Unit;

class Span
{
    public const MAX_FLAGS = 10;

    protected $spanId;

    protected $traceId;

    protected $description;

    protected $op;

    protected $status;

    protected $parentSpanId;

    protected $sampled;

    protected $tags = [];

    protected $flags = [];

    protected $data = [];

    protected $startTimestamp;

    protected $endTimestamp;

    protected $spanRecorder;

    protected $transaction;

    protected $origin;

    public function __construct(?SpanContext $context = null)
    {
        if ($context === null) {
            $this->traceId = TraceId::generate();
            $this->spanId = SpanId::generate();
            $this->startTimestamp = microtime(true);

            return;
        }

        $this->traceId = $context->getTraceId() ?? TraceId::generate();
        $this->spanId = $context->getSpanId() ?? SpanId::generate();
        $this->startTimestamp = $context->getStartTimestamp() ?? microtime(true);
        $this->parentSpanId = $context->getParentSpanId();
        $this->description = $context->getDescription();
        $this->op = $context->getOp();
        $this->status = $context->getStatus();
        $this->sampled = $context->getSampled();
        $this->tags = $context->getTags();
        $this->data = $context->getData();
        $this->endTimestamp = $context->getEndTimestamp();
        $this->origin = $context->getOrigin();
    }

    public function setSpanId(SpanId $spanId): self
    {
        $this->spanId = $spanId;

        return $this;
    }

    public function getTraceId(): TraceId
    {
        return $this->traceId;
    }

    public function setTraceId(TraceId $traceId)
    {
        $this->traceId = $traceId;

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

    public function getStartTimestamp(): float
    {
        return $this->startTimestamp;
    }

    public function setStartTimestamp(float $startTimestamp)
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    public function getEndTimestamp(): ?float
    {
        return $this->endTimestamp;
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

    public function setHttpStatus(int $statusCode)
    {
        SentrySdk::getCurrentHub()->configureScope(static function (Scope $scope) use ($statusCode) {
            $scope->setContext('response', [
                'status_code' => $statusCode,
            ]);
        });

        $status = SpanStatus::createFromHttpStatusCode($statusCode);

        if ($status !== SpanStatus::unknownError()) {
            $this->status = $status;
        }

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }

    public function setFlag(string $key, bool $result)
    {
        if (\count($this->flags) < self::MAX_FLAGS) {
            $this->flags[$key] = $result;
        }

        return $this;
    }

    public function getSpanId(): SpanId
    {
        return $this->spanId;
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

    public function getData(?string $key = null, $default = null)
    {
        if ($key === null) {
            $data = $this->data;

            foreach ($this->flags as $flagKey => $flagValue) {
                $data["flag.evaluation.{$flagKey}"] = $flagValue;
            }

            return $data;
        }

        return $this->data[$key] ?? $default;
    }

    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function getTraceContext(): array
    {
        $result = [
            'span_id' => (string) $this->spanId,
            'trace_id' => (string) $this->traceId,
            'origin' => $this->origin ?? 'manual',
        ];

        if ($this->parentSpanId !== null) {
            $result['parent_span_id'] = (string) $this->parentSpanId;
        }

        if ($this->description !== null) {
            $result['description'] = $this->description;
        }

        if ($this->op !== null) {
            $result['op'] = $this->op;
        }

        if ($this->status !== null) {
            $result['status'] = (string) $this->status;
        }

        if (!empty($this->data)) {
            $result['data'] = $this->data;
        }

        if (!empty($this->tags)) {
            $result['tags'] = $this->tags;
        }

        return $result;
    }

    public function finish(?float $endTimestamp = null): ?EventId
    {
        $this->endTimestamp = $endTimestamp ?? microtime(true);

        return null;
    }

    public function startChild(SpanContext $context): self
    {
        $context = clone $context;
        $context->setSampled($this->sampled);
        $context->setParentSpanId($this->spanId);
        $context->setTraceId($this->traceId);

        $span = new self($context);
        $span->transaction = $this->transaction;
        $span->spanRecorder = $this->spanRecorder;

        if ($span->spanRecorder !== null) {
            $span->spanRecorder->add($span);
        }

        return $span;
    }

    public function getSpanRecorder(): ?SpanRecorder
    {
        return $this->spanRecorder;
    }

    public function detachSpanRecorder()
    {
        $this->spanRecorder = null;

        return $this;
    }

    public function getMetricsSummary(): array
    {
        return [];
    }

    public function setMetricsSummary(
        string $type,
        string $key,
        $value,
        Unit $unit,
        array $tags,
    ): void {
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

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function toTraceparent(): string
    {
        $sampled = '';

        if ($this->sampled !== null) {
            $sampled = $this->sampled ? '-1' : '-0';
        }

        return \sprintf('%s-%s%s', (string) $this->traceId, (string) $this->spanId, $sampled);
    }

    public function toW3CTraceparent(): string
    {
        return '';
    }

    public function toBaggage(): string
    {
        $transaction = $this->getTransaction();

        if ($transaction !== null) {
            return (string) $transaction->getDynamicSamplingContext();
        }

        return '';
    }
}
