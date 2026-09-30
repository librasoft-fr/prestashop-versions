<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\String_;


final class NonEmptyLowercaseString extends String_ implements PseudoType
{
    public function underlyingType(): Type
    {
        return new String_();
    }

    
    public function __toString(): string
    {
        return 'non-empty-lowercase-string';
    }
}
