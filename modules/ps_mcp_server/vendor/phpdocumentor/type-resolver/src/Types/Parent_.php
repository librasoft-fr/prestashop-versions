<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Parent_ implements Type
{
    
    public function __toString(): string
    {
        return 'parent';
    }
}
