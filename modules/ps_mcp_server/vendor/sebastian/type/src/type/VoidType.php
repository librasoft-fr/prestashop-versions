<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

final class VoidType extends Type
{
    public function isAssignable(Type $other): bool
    {
        return $other instanceof self;
    }

    public function name(): string
    {
        return 'void';
    }

    public function allowsNull(): bool
    {
        return false;
    }

    
    public function isVoid(): bool
    {
        return true;
    }
}
