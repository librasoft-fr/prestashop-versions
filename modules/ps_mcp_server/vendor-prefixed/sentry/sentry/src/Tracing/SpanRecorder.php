<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

final class SpanRecorder
{
    private $maxSpans;

    private $spans = [];

    public function __construct(int $maxSpans = 1000)
    {
        $this->maxSpans = $maxSpans;
    }

    public function add(Span $span): self
    {
        if (\count($this->spans) > $this->maxSpans) {
            $span->detachSpanRecorder();
        } else {
            $this->spans[] = $span;
        }

        return $this;
    }

    public function getSpans(): array
    {
        return $this->spans;
    }
}
