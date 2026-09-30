<?php declare(strict_types=1);

namespace SebastianBergmann\Type;

use function is_subclass_of;
use function strcasecmp;

final class StaticType extends Type
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

        if (!$other instanceof ObjectType) {
            return false;
        }

        if (0 === strcasecmp($this->className->qualifiedName(), $other->className()->qualifiedName())) {
            return true;
        }

        if (is_subclass_of($other->className()->qualifiedName(), $this->className->qualifiedName(), true)) {
            return true;
        }

        return false;
    }

    public function name(): string
    {
        return 'static';
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    
    public function isStatic(): bool
    {
        return true;
    }
}
