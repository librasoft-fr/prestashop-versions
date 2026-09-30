<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use Countable;
use IteratorAggregate;
use function count;


class BundledComponentCollection implements Countable, IteratorAggregate {
    
    private $bundledComponents = [];

    public function add(BundledComponent $bundledComponent): void {
        $this->bundledComponents[] = $bundledComponent;
    }

    
    public function getBundledComponents(): array {
        return $this->bundledComponents;
    }

    public function count(): int {
        return count($this->bundledComponents);
    }

    public function getIterator(): BundledComponentCollectionIterator {
        return new BundledComponentCollectionIterator($this);
    }
}
