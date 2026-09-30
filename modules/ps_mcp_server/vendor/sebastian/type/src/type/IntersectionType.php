<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

use function assert;
use function count;
use function implode;
use function in_array;
use function sort;

final class IntersectionType extends Type
{
    
    private array $types;

    
    public function __construct(Type ...$types)
    {
        $this->ensureMinimumOfTwoTypes(...$types);
        $this->ensureOnlyValidTypes(...$types);
        $this->ensureNoDuplicateTypes(...$types);

        $this->types = $types;
    }

    public function isAssignable(Type $other): bool
    {
        return $other->isObject();
    }

    public function asString(): string
    {
        return $this->name();
    }

    public function name(): string
    {
        $types = [];

        foreach ($this->types as $type) {
            $types[] = $type->name();
        }

        sort($types);

        return implode('&', $types);
    }

    public function allowsNull(): bool
    {
        return false;
    }

    
    public function isIntersection(): bool
    {
        return true;
    }

    
    public function types(): array
    {
        return $this->types;
    }

    
    private function ensureMinimumOfTwoTypes(Type ...$types): void
    {
        if (count($types) < 2) {
            throw new RuntimeException(
                'An intersection type must be composed of at least two types'
            );
        }
    }

    
    private function ensureOnlyValidTypes(Type ...$types): void
    {
        foreach ($types as $type) {
            if (!$type->isObject()) {
                throw new RuntimeException(
                    'An intersection type can only be composed of interfaces and classes'
                );
            }
        }
    }

    
    private function ensureNoDuplicateTypes(Type ...$types): void
    {
        $names = [];

        foreach ($types as $type) {
            assert($type instanceof ObjectType);

            $classQualifiedName = $type->className()->qualifiedName();

            if (in_array($classQualifiedName, $names, true)) {
                throw new RuntimeException('An intersection type must not contain duplicate types');
            }

            $names[] = $classQualifiedName;
        }
    }
}
