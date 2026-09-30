<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;


final class NonEmptyArray extends Array_ implements PseudoType
{
    public function underlyingType(): Type
    {
        return new Array_($this->valueType, $this->keyType);
    }

    
    public function __toString(): string
    {
        if ($this->keyType) {
            return 'non-empty-array<' . $this->keyType . ',' . $this->valueType . '>';
        }

        if ($this->valueType instanceof Mixed_) {
            return 'non-empty-array';
        }

        return 'non-empty-array<' . $this->valueType . '>';
    }
}
