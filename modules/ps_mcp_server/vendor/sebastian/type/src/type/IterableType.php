<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

use function assert;
use function class_exists;
use function is_iterable;
use ReflectionClass;

final class IterableType extends Type
{
    private bool $allowsNull;

    public function __construct(bool $nullable)
    {
        $this->allowsNull = $nullable;
    }

    
    public function isAssignable(Type $other): bool
    {
        if ($this->allowsNull && $other instanceof NullType) {
            return true;
        }

        if ($other instanceof self) {
            return true;
        }

        if ($other instanceof SimpleType) {
            return is_iterable($other->value());
        }

        if ($other instanceof ObjectType) {
            $className = $other->className()->qualifiedName();

            assert(class_exists($className));

            return (new ReflectionClass($className))->isIterable();
        }

        return false;
    }

    public function name(): string
    {
        return 'iterable';
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    
    public function isIterable(): bool
    {
        return true;
    }
}
