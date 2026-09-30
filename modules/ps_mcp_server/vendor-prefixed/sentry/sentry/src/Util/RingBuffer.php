<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

class RingBuffer implements \Countable
{
    private $buffer;

    private $capacity;

    private $head = 0;

    private $tail = 0;

    private $count = 0;

    public function __construct(int $capacity)
    {
        if ($capacity <= 0) {
            throw new \RuntimeException('RingBuffer capacity must be greater than 0');
        }
        $this->capacity = $capacity;
        $this->buffer = new \SplFixedArray($capacity);
    }

    public function capacity(): int
    {
        return $this->capacity;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    public function isFull(): bool
    {
        return $this->count === $this->capacity;
    }

    public function push($value): void
    {
        $this->buffer[$this->tail] = $value;

        $this->tail = ($this->tail + 1) % $this->capacity;

        if ($this->isFull()) {
            $this->head = ($this->head + 1) % $this->capacity;
        } else {
            ++$this->count;
        }
    }

    public function shift()
    {
        if ($this->isEmpty()) {
            return null;
        }
        $value = $this->buffer[$this->head];

        $this->buffer[$this->head] = null;

        $this->head = ($this->head + 1) % $this->capacity;
        --$this->count;

        return $value;
    }

    public function peekBack()
    {
        if ($this->isEmpty()) {
            return null;
        }
        $idx = ($this->tail - 1 + $this->capacity) % $this->capacity;

        return $this->buffer[$idx];
    }

    public function peekFront()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->buffer[$this->head];
    }

    public function clear(): void
    {
        for ($i = 0; $i < $this->count; ++$i) {
            $this->buffer[($this->head + $i) % $this->capacity] = null;
        }
        $this->count = 0;
        $this->head = 0;
        $this->tail = 0;
    }

    public function toArray(): array
    {
        $result = [];
        for ($i = 0; $i < $this->count; ++$i) {
            $value = $this->buffer[($this->head + $i) % $this->capacity];

            $result[] = $value;
        }

        return $result;
    }

    public function drain(): array
    {
        $result = $this->toArray();
        $this->clear();

        return $result;
    }
}
