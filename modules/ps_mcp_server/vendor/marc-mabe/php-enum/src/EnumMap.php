<?php

declare(strict_types=1);

namespace MabeEnum;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use UnexpectedValueException;


class EnumMap implements ArrayAccess, Countable, IteratorAggregate
{
    
    private $enumeration;

    
    private $map = [];

    
    public function __construct(string $enumeration, ?iterable $map = null)
    {
        if (!\is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(\sprintf(
                '%s can handle subclasses of %s only',
                 __CLASS__,
                Enum::class
            ));
        }
        $this->enumeration = $enumeration;

        if ($map) {
            $this->addIterable($map);
        }
    }

    
    public function __debugInfo(): array {
        $dbg = (array)$this;
        $dbg["\0" . self::class . "\0__pairs"] = array_map(function ($k, $v) {
            return [$k, $v];
        }, $this->getKeys(), $this->getValues());
        return $dbg;
    }

    

    
    public function add($enumerator, $value): void
    {
        $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
        $this->map[$ord] = $value;
    }

    
    public function addIterable(iterable $map): void
    {
        $innerMap = $this->map;
        foreach ($map as $enumerator => $value) {
            $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
            $innerMap[$ord] = $value;
        }
        $this->map = $innerMap;
    }

    
    public function remove($enumerator): void
    {
        $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
        unset($this->map[$ord]);
    }

    
    public function removeIterable(iterable $enumerators): void
    {
        $map = $this->map;
        foreach ($enumerators as $enumerator) {
            $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
            unset($map[$ord]);
        }

        $this->map = $map;
    }

    

    
    public function with($enumerator, $value): self
    {
        $clone = clone $this;
        $clone->add($enumerator, $value);
        return $clone;
    }

    
    public function withIterable(iterable $map): self
    {
        $clone = clone $this;
        $clone->addIterable($map);
        return $clone;
    }

    
    public function without($enumerator): self
    {
        $clone = clone $this;
        $clone->remove($enumerator);
        return $clone;
    }

    
    public function withoutIterable(iterable $enumerators): self
    {
        $clone = clone $this;
        $clone->removeIterable($enumerators);
        return $clone;
    }

    

    
    public function getEnumeration(): string
    {
        return $this->enumeration;
    }

    
    public function get($enumerator)
    {
        $enumerator = ($this->enumeration)::get($enumerator);
        $ord = $enumerator->getOrdinal();
        if (!\array_key_exists($ord, $this->map)) {
            throw new UnexpectedValueException(sprintf(
                'Enumerator %s could not be found',
                \var_export($enumerator->getValue(), true)
            ));
        }

        return $this->map[$ord];
    }

    
    public function getKeys(): array
    {
        
        $byOrdinalFn = [$this->enumeration, 'byOrdinal'];

        return \array_map($byOrdinalFn, \array_keys($this->map));
    }

    
    public function getValues(): array
    {
        return \array_values($this->map);
    }

    
    public function search($value, bool $strict = false)
    {
        
        $ord = \array_search($value, $this->map, $strict);
        if ($ord !== false) {
            return ($this->enumeration)::byOrdinal($ord);
        }

        return null;
    }

    
    public function has($enumerator): bool
    {
        try {
            $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
            return \array_key_exists($ord, $this->map);
        } catch (InvalidArgumentException $e) {
            
            return false;
        }
    }

    
    public function contains($enumerator): bool
    {
        return $this->has($enumerator);
    }

    

    
    public function offsetExists($enumerator): bool
    {
        try {
            return isset($this->map[($this->enumeration)::get($enumerator)->getOrdinal()]);
        } catch (InvalidArgumentException $e) {
            
            return false;
        }
    }

    
    #[\ReturnTypeWillChange]
    public function offsetGet($enumerator)
    {
        try {
            return $this->get($enumerator);
        } catch (UnexpectedValueException $e) {
            return null;
        }
    }

    
    public function offsetSet($enumerator, $value = null): void
    {
        $this->add($enumerator, $value);
    }

    
    public function offsetUnset($enumerator): void
    {
        $this->remove($enumerator);
    }

    

    
    public function getIterator(): Iterator
    {
        $map = $this->map;
        foreach ($map as $ordinal => $value) {
            yield ($this->enumeration)::byOrdinal($ordinal) => $value;
        }
    }

    

    
    public function count(): int
    {
        return \count($this->map);
    }

    
    public function isEmpty(): bool
    {
        return empty($this->map);
    }
}
