<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Types;

use PsMcpServerDeps\phpDocumentor\Reflection\Type;

final class Expression implements Type
{
    protected $valueType;

    public function __construct(Type $valueType)
    {
        $this->valueType = $valueType;
    }

    public function getValueType(): Type
    {
        return $this->valueType;
    }

    public function __toString(): string
    {
        return '(' . $this->valueType . ')';
    }
}
