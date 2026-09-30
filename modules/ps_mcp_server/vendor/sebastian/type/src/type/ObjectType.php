<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

use function is_subclass_of;
use function strcasecmp;

final class ObjectType extends Type
{
    private TypeName $className;
    private bool $allowsNull;

    public function __construct(TypeName $className, bool $allowsNull)
    {
        $this->className  = $className;
        $this->allowsNull = $allowsNull;
    }

    public function isAssignable(Type $other): bool
    {
        if ($this->allowsNull && $other instanceof NullType) {
            return true;
        }

        if ($other instanceof self) {
            if (0 === strcasecmp($this->className->qualifiedName(), $other->className->qualifiedName())) {
                return true;
            }

            if (is_subclass_of($other->className->qualifiedName(), $this->className->qualifiedName(), true)) {
                return true;
            }
        }

        return false;
    }

    public function name(): string
    {
        return $this->className->qualifiedName();
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    public function className(): TypeName
    {
        return $this->className;
    }

    
    public function isObject(): bool
    {
        return true;
    }
}
