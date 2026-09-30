<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use Iterator;
use function count;


class BundledComponentCollectionIterator implements Iterator {
    
    private $bundledComponents;

    
    private $position = 0;

    public function __construct(BundledComponentCollection $bundledComponents) {
        $this->bundledComponents = $bundledComponents->getBundledComponents();
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function valid(): bool {
        return $this->position < count($this->bundledComponents);
    }

    public function key(): int {
        return $this->position;
    }

    public function current(): BundledComponent {
        return $this->bundledComponents[$this->position];
    }

    public function next(): void {
        $this->position++;
    }
}
