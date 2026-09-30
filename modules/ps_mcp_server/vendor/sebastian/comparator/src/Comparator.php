<?php declare(strict_types=1);

namespace SebastianBergmann\Comparator;

abstract class Comparator
{
    private Factory $factory;

    public function setFactory(Factory $factory): void
    {
        $this->factory = $factory;
    }

    abstract public function accepts(mixed $expected, mixed $actual): bool;

    
    abstract public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false): void;

    protected function factory(): Factory
    {
        return $this->factory;
    }
}
