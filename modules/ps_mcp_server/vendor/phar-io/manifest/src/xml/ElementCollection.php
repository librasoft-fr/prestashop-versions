<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use DOMElement;
use DOMNodeList;
use Iterator;
use ReturnTypeWillChange;
use function count;
use function get_class;
use function sprintf;


abstract class ElementCollection implements Iterator {
    
    private $nodes = [];

    
    private $position;

    public function __construct(DOMNodeList $nodeList) {
        $this->position = 0;
        $this->importNodes($nodeList);
    }

    #[ReturnTypeWillChange]
    abstract public function current();

    public function next(): void {
        $this->position++;
    }

    public function key(): int {
        return $this->position;
    }

    public function valid(): bool {
        return $this->position < count($this->nodes);
    }

    public function rewind(): void {
        $this->position = 0;
    }

    protected function getCurrentElement(): DOMElement {
        return $this->nodes[$this->position];
    }

    private function importNodes(DOMNodeList $nodeList): void {
        foreach ($nodeList as $node) {
            if (!$node instanceof DOMElement) {
                throw new ElementCollectionException(
                    sprintf('\DOMElement expected, got \%s', get_class($node))
                );
            }

            $this->nodes[] = $node;
        }
    }
}
