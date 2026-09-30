<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Callable_;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Nullable;

final class OffsetAccess extends Mixed_ implements PseudoType
{
    private $type;

    private $offset;

    public function __construct(Type $type, Type $offset)
    {
        $this->type = $type;
        $this->offset = $offset;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getOffset(): Type
    {
        return $this->offset;
    }

    public function underlyingType(): Type
    {
        return new Mixed_();
    }

    public function __toString(): string
    {
        if (
            $this->type instanceof Callable_
            || $this->type instanceof ConstExpression
            || $this->type instanceof Nullable
        ) {
            return '(' . $this->type . ')[' . $this->offset . ']';
        }

        return $this->type . '[' . $this->offset . ']';
    }
}
