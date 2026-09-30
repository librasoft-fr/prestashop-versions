<?php declare(strict_types=1);

namespace SebastianBergmann\Complexity;

use Iterator;

final class ComplexityCollectionIterator implements Iterator
{
    
    private readonly array $items;
    private int $position = 0;

    public function __construct(ComplexityCollection $items)
    {
        $this->items = $items->asArray();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): Complexity
    {
        return $this->items[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
