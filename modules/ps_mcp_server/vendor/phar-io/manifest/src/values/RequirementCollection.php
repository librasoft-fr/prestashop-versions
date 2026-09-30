<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use Countable;
use IteratorAggregate;
use function count;


class RequirementCollection implements Countable, IteratorAggregate {
    
    private $requirements = [];

    public function add(Requirement $requirement): void {
        $this->requirements[] = $requirement;
    }

    
    public function getRequirements(): array {
        return $this->requirements;
    }

    public function count(): int {
        return count($this->requirements);
    }

    public function getIterator(): RequirementCollectionIterator {
        return new RequirementCollectionIterator($this);
    }
}
