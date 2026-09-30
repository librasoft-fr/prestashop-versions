<?php


declare(strict_types=1);

namespace phpDocumentor\Reflection\Types;

use ArrayIterator;
use IteratorAggregate;
use phpDocumentor\Reflection\Type;

use function array_key_exists;
use function implode;


abstract class AggregatedType implements Type, IteratorAggregate
{
    
    private $types = [];

    
    private $token;

    
    public function __construct(array $types, string $token)
    {
        foreach ($types as $type) {
            $this->add($type);
        }

        $this->token = $token;
    }

    
    public function get(int $index): ?Type
    {
        if (!$this->has($index)) {
            return null;
        }

        return $this->types[$index];
    }

    
    public function has(int $index): bool
    {
        return array_key_exists($index, $this->types);
    }

    
    public function contains(Type $type): bool
    {
        foreach ($this->types as $typePart) {
            
            if ((string) $typePart === (string) $type) {
                return true;
            }
        }

        return false;
    }

    
    public function __toString(): string
    {
        return implode($this->token, $this->types);
    }

    
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->types);
    }

    
    private function add(Type $type): void
    {
        if ($type instanceof static) {
            foreach ($type->getIterator() as $subType) {
                $this->add($subType);
            }

            return;
        }

        
        if ($this->contains($type)) {
            return;
        }

        $this->types[] = $type;
    }
}
