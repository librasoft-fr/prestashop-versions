<?php

declare(strict_types=1);



namespace JsonSchema\Iterator;


class ObjectIterator implements \Iterator, \Countable
{
    
    private $object;

    
    private $position = 0;

    
    private $data = [];

    
    private $initialized = false;

    
    public function __construct($object)
    {
        $this->object = $object;
    }

    
    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->initialize();

        return $this->data[$this->position];
    }

    
    public function next(): void
    {
        $this->initialize();
        $this->position++;
    }

    
    public function key(): int
    {
        $this->initialize();

        return $this->position;
    }

    
    public function valid(): bool
    {
        $this->initialize();

        return isset($this->data[$this->position]);
    }

    
    public function rewind(): void
    {
        $this->initialize();
        $this->position = 0;
    }

    
    public function count(): int
    {
        $this->initialize();

        return count($this->data);
    }

    
    private function initialize()
    {
        if (!$this->initialized) {
            $this->data = $this->buildDataFromObject($this->object);
            $this->initialized = true;
        }
    }

    
    private function buildDataFromObject($object)
    {
        $result = [];

        $stack = new \SplStack();
        $stack->push($object);

        while (!$stack->isEmpty()) {
            $current = $stack->pop();
            if (is_object($current)) {
                array_push($result, $current);
            }

            foreach ($this->getDataFromItem($current) as $propertyName => $propertyValue) {
                if (is_object($propertyValue) || is_array($propertyValue)) {
                    $stack->push($propertyValue);
                }
            }
        }

        return $result;
    }

    
    private function getDataFromItem($item)
    {
        if (!is_object($item) && !is_array($item)) {
            return [];
        }

        return is_object($item) ? get_object_vars($item) : $item;
    }
}
