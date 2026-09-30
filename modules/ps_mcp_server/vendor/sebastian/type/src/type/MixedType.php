<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

final class MixedType extends Type
{
    public function isAssignable(Type $other): bool
    {
        return !$other instanceof VoidType;
    }

    public function asString(): string
    {
        return 'mixed';
    }

    public function name(): string
    {
        return 'mixed';
    }

    public function allowsNull(): bool
    {
        return true;
    }

    
    public function isMixed(): bool
    {
        return true;
    }
}
