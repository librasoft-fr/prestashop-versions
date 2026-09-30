<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

final class FalseType extends Type
{
    public function isAssignable(Type $other): bool
    {
        if ($other instanceof self) {
            return true;
        }

        return $other instanceof SimpleType &&
              $other->name() === 'bool' &&
              $other->value() === false;
    }

    public function name(): string
    {
        return 'false';
    }

    public function allowsNull(): bool
    {
        return false;
    }

    
    public function isFalse(): bool
    {
        return true;
    }
}
