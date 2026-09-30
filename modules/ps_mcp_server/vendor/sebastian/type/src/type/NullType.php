<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

final class NullType extends Type
{
    public function isAssignable(Type $other): bool
    {
        return !($other instanceof VoidType);
    }

    public function name(): string
    {
        return 'null';
    }

    public function asString(): string
    {
        return 'null';
    }

    public function allowsNull(): bool
    {
        return true;
    }

    
    public function isNull(): bool
    {
        return true;
    }
}
