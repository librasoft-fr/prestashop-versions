<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


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
