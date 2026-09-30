<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

final class UnknownType extends Type
{
    public function isAssignable(Type $other): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'unknown type';
    }

    public function asString(): string
    {
        return '';
    }

    public function allowsNull(): bool
    {
        return true;
    }

    
    public function isUnknown(): bool
    {
        return true;
    }
}
