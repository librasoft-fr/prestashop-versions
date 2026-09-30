<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

final class TrueType extends Type
{
    public function isAssignable(Type $other): bool
    {
        if ($other instanceof self) {
            return true;
        }

        return $other instanceof SimpleType &&
              $other->name() === 'bool' &&
              $other->value() === true;
    }

    public function name(): string
    {
        return 'true';
    }

    public function allowsNull(): bool
    {
        return false;
    }

    
    public function isTrue(): bool
    {
        return true;
    }
}
