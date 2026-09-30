<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Nullable implements Type
{
    
    private $realType;

    
    public function __construct(Type $realType)
    {
        $this->realType = $realType;
    }

    
    public function getActualType(): Type
    {
        return $this->realType;
    }

    
    public function __toString(): string
    {
        return '?' . $this->realType->__toString();
    }
}
