<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

use PsMcpServerDeps\Sentry\Event;
use PsMcpServerDeps\Sentry\EventId;
use PsMcpServerDeps\Sentry\Profiling\Profiler;
use PsMcpServerDeps\Sentry\SentrySdk;
use PsMcpServerDeps\Sentry\State\HubInterface;

final class Transaction extends Span
{
    private $hub;

    private $name;

    protected $transaction;

    protected $metadata;

    protected $profiler;

    public function __construct(TransactionContext $context, ?HubInterface $hub = null)
    {
        parent::__construct($context);

        $this->hub = $hub ?? SentrySdk::getCurrentHub();
        $this->name = $context->getName();
        $this->metadata = $context->getMetadata();
        $this->transaction = $this;
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

    public function getMetadata(): TransactionMetadata
    {
        return $this->metadata;
    }

    public function getDynamicSamplingContext(): DynamicSamplingContext
    {
        if ($this->metadata->getDynamicSamplingContext() !== null) {
            return $this->metadata->getDynamicSamplingContext();
        }

        $samplingContext = DynamicSamplingContext::fromTransaction($this->transaction, $this->hub);
        $this->getMetadata()->setDynamicSamplingContext($samplingContext);

        return $samplingContext;
    }

    public function initSpanRecorder(int $maxSpans = 1000): self
    {
        if ($this->spanRecorder === null) {
            $this->spanRecorder = new SpanRecorder($maxSpans);
        }

        $this->spanRecorder->add($this);

        return $this;
    }

    public function initProfiler(): Profiler
    {
        if ($this->profiler === null) {
            $client = $this->hub->getClient();
            $options = $client !== null ? $client->getOptions() : null;

            $this->profiler = new Profiler($options);
        }

        return $this->profiler;
    }

    public function getProfiler(): ?Profiler
    {
        return $this->profiler;
    }

    public function detachProfiler(): self
    {
        $this->profiler = null;

        return $this;
    }

    public function finish(?float $endTimestamp = null): ?EventId
    {
        if ($this->profiler !== null) {
            $this->profiler->stop();
        }

        if ($this->endTimestamp !== null) {
            return null;
        }

        parent::finish($endTimestamp);

        if ($this->sampled !== true) {
            return null;
        }

        $finishedSpans = [];

        if ($this->spanRecorder !== null) {
            foreach ($this->spanRecorder->getSpans() as $span) {
                if ($span->getSpanId() !== $this->getSpanId() && $span->getEndTimestamp() !== null) {
                    $finishedSpans[] = $span;
                }
            }
        }

        $event = Event::createTransaction();
        $event->setSpans($finishedSpans);
        $event->setStartTimestamp($this->startTimestamp);
        $event->setTimestamp($this->endTimestamp);
        $event->setTags($this->tags);
        $event->setTransaction($this->name);
        $event->setContext('trace', $this->getTraceContext());
        $event->setSdkMetadata('dynamic_sampling_context', $this->getDynamicSamplingContext());
        $event->setSdkMetadata('transaction_metadata', $this->getMetadata());

        if ($this->profiler !== null) {
            $profile = $this->profiler->getProfile();
            if ($profile !== null) {
                $event->setSdkMetadata('profile', $profile);
            }
        }

        return $this->hub->captureEvent($event);
    }
}
