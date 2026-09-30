<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use Iterator;
use function count;


class RequirementCollectionIterator implements Iterator {
    
    private $requirements;

    
    private $position = 0;

    public function __construct(RequirementCollection $requirements) {
        $this->requirements = $requirements->getRequirements();
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function valid(): bool {
        return $this->position < count($this->requirements);
    }

    public function key(): int {
        return $this->position;
    }

    public function current(): Requirement {
        return $this->requirements[$this->position];
    }

    public function next(): void {
        $this->position++;
    }
}
