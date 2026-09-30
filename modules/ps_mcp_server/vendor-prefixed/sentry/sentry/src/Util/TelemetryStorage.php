<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

class TelemetryStorage implements \Countable
{
    private $data;

    private function __construct(?int $size = null)
    {
        if ($size !== null) {
            $this->data = new RingBuffer($size);
        } else {
            $this->data = [];
        }
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function push($value): void
    {
        if ($this->data instanceof RingBuffer) {
            $this->data->push($value);
        } else {
            $this->data[] = $value;
        }
    }

    public function drain(): array
    {
        if ($this->data instanceof RingBuffer) {
            return $this->data->drain();
        }
        $data = $this->data;
        $this->data = [];

        return $data;
    }

    public function toArray(): array
    {
        if ($this->data instanceof RingBuffer) {
            return $this->data->toArray();
        }

        return $this->data;
    }

    public function isEmpty(): bool
    {
        if ($this->data instanceof RingBuffer) {
            return $this->data->isEmpty();
        }

        return empty($this->data);
    }

    public static function unbounded(): self
    {
        return new self();
    }

    public static function bounded(int $size): self
    {
        return new self($size);
    }
}
