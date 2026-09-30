<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

use PsMcpServerDeps\Sentry\SentrySdk;
use PsMcpServerDeps\Sentry\State\Scope;
use PsMcpServerDeps\Sentry\Tracing\Traits\TraceHeaderParserTrait;

final class PropagationContext
{
    use TraceHeaderParserTrait;

    private $traceId;

    private $spanId;

    private $parentSpanId;

    private $parentSampled;

    private $sampleRand;

    private $dynamicSamplingContext;

    private function __construct()
    {
    }

    public static function fromDefaults(): self
    {
        $context = new self();

        $context->traceId = TraceId::generate();
        $context->spanId = SpanId::generate();
        $context->parentSpanId = null;
        $context->parentSampled = null;
        $context->sampleRand = round(mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax(), 6);
        $context->dynamicSamplingContext = null;

        return $context;
    }

    public static function fromHeaders(string $sentryTraceHeader, string $baggageHeader): self
    {
        return self::parseTraceparentAndBaggage($sentryTraceHeader, $baggageHeader);
    }

    public static function fromEnvironment(string $sentryTrace, string $baggage): self
    {
        return self::parseTraceparentAndBaggage($sentryTrace, $baggage);
    }

    public function toTraceparent(): string
    {
        return \sprintf('%s-%s', (string) $this->traceId, (string) $this->spanId);
    }

    public function toW3CTraceparent(): string
    {
        return '';
    }

    public function toBaggage(): string
    {
        if ($this->dynamicSamplingContext === null) {
            $hub = SentrySdk::getCurrentHub();
            $client = $hub->getClient();

            if ($client !== null) {
                $options = $client->getOptions();

                if ($options !== null) {
                    $hub->configureScope(function (Scope $scope) use ($options) {
                        $this->dynamicSamplingContext = DynamicSamplingContext::fromOptions($options, $scope);
                    });
                }
            }
        }

        return (string) $this->dynamicSamplingContext;
    }

    public function getTraceContext(): array
    {
        $result = [
            'trace_id' => (string) $this->traceId,
            'span_id' => (string) $this->spanId,
        ];

        if ($this->parentSpanId !== null) {
            $result['parent_span_id'] = (string) $this->parentSpanId;
        }

        return $result;
    }

    public function getTraceId(): TraceId
    {
        return $this->traceId;
    }

    public function setTraceId(TraceId $traceId): void
    {
        $this->traceId = $traceId;
    }

    public function getParentSpanId(): ?SpanId
    {
        return $this->parentSpanId;
    }

    public function setParentSpanId(?SpanId $parentSpanId): void
    {
        $this->parentSpanId = $parentSpanId;
    }

    public function getSpanId(): SpanId
    {
        return $this->spanId;
    }

    public function setSpanId(SpanId $spanId): self
    {
        $this->spanId = $spanId;

        return $this;
    }

    public function getDynamicSamplingContext(): ?DynamicSamplingContext
    {
        return $this->dynamicSamplingContext;
    }

    public function setDynamicSamplingContext(DynamicSamplingContext $dynamicSamplingContext): self
    {
        $this->dynamicSamplingContext = $dynamicSamplingContext;

        return $this;
    }

    public function getSampleRand(): ?float
    {
        return $this->sampleRand;
    }

    public function setSampleRand(?float $sampleRand): self
    {
        $this->sampleRand = $sampleRand;

        return $this;
    }

    private static function parseTraceparentAndBaggage(string $traceparent, string $baggage): self
    {
        $context = self::fromDefaults();
        $parsedData = self::parseTraceAndBaggageHeaders($traceparent, $baggage);

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
            $context->dynamicSamplingContext = $parsedData['dynamicSamplingContext'];
        }

        if ($parsedData['sampleRand'] !== null) {
            $context->sampleRand = $parsedData['sampleRand'];
        }

        return $context;
    }
}
