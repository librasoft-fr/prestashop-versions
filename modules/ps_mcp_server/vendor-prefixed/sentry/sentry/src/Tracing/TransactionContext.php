<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

use PsMcpServerDeps\Sentry\Tracing\Traits\TraceHeaderParserTrait;

final class TransactionContext extends SpanContext
{
    use TraceHeaderParserTrait;

    public const DEFAULT_NAME = '<unlabeled transaction>';

    private $name;

    private $parentSampled;

    private $metadata;

    public function __construct(
        string $name = self::DEFAULT_NAME,
        ?bool $parentSampled = null,
        ?TransactionMetadata $metadata = null,
    ) {
        $this->name = $name;
        $this->parentSampled = $parentSampled;
        $this->metadata = $metadata ?? new TransactionMetadata();
    }

    public static function make()
    {
        return new self();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParentSampled(): ?bool
    {
        return $this->parentSampled;
    }

    public function setParentSampled(?bool $parentSampled): self
    {
        $this->parentSampled = $parentSampled;

        return $this;
    }

    public function getMetadata(): TransactionMetadata
    {
        return $this->metadata;
    }

    public function setMetadata(TransactionMetadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function setSource(TransactionSource $transactionSource): self
    {
        $this->metadata->setSource($transactionSource);

        return $this;
    }

    public static function fromEnvironment(string $sentryTrace, string $baggage): self
    {
        return self::parseTraceAndBaggage($sentryTrace, $baggage);
    }

    public static function fromHeaders(string $sentryTraceHeader, string $baggageHeader): self
    {
        return self::parseTraceAndBaggage($sentryTraceHeader, $baggageHeader);
    }

    private static function parseTraceAndBaggage(string $sentryTrace, string $baggage): self
    {
        $context = new self();
        $parsedData = self::parseTraceAndBaggageHeaders($sentryTrace, $baggage);

        if ($parsedData['traceId'] !== null) {
            $context->traceId = $parsedData['traceId'];
        }

        if ($parsedData['parentSpanId'] !== null) {
            $context->parentSpanId = $parsedData['parentSpanId'];
        }

        if ($parsedData['parentSampled'] !== null) {
            $context->parentSampled = $parsedData['parentSampled'];
        }

        if ($parsedData['dynamicSamplingContext'] !== null) {
            $context->getMetadata()->setDynamicSamplingContext($parsedData['dynamicSamplingContext']);
        }

        if ($parsedData['parentSamplingRate'] !== null) {
            $context->getMetadata()->setParentSamplingRate($parsedData['parentSamplingRate']);
        }

        if ($parsedData['sampleRand'] !== null) {
            $context->getMetadata()->setSampleRand($parsedData['sampleRand']);
        }

        return $context;
    }
}
