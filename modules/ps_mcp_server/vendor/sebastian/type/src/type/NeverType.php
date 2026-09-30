<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

final class NeverType extends Type
{
    public function isAssignable(Type $other): bool
    {
        return $other instanceof self;
    }

    public function name(): string
    {
        return 'never';
    }

    public function allowsNull(): bool
    {
        return false;
    }

    
    public function isNever(): bool
    {
        return true;
    }
}
