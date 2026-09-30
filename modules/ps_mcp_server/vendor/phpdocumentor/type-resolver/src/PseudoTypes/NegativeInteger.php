<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Integer;


final class NegativeInteger extends Integer implements PseudoType
{
    public function underlyingType(): Type
    {
        return new Integer();
    }

    
    public function __toString(): string
    {
        return 'negative-int';
    }
}
