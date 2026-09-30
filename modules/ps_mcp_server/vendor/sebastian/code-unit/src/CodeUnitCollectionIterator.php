<?php declare(strict_types=1);

namespace SebastianBergmann\CodeUnit;

use Iterator;


final class CodeUnitCollectionIterator implements Iterator
{
    
    private array $codeUnits;
    private int $position = 0;

    public function __construct(CodeUnitCollection $collection)
    {
        $this->codeUnits = $collection->asArray();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->codeUnits[$this->position]);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): CodeUnit
    {
        return $this->codeUnits[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
