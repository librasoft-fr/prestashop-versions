<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


class Boolean implements Type
{
    
    public function __toString(): string
    {
        return 'bool';
    }
}
