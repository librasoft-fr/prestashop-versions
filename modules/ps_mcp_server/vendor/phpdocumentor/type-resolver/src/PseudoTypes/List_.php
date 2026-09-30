<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;


final class List_ extends Array_ implements PseudoType
{
    public function underlyingType(): Type
    {
        return new Array_();
    }

    public function __construct(?Type $valueType = null)
    {
        parent::__construct($valueType, new Integer());
    }

    
    public function __toString(): string
    {
        if ($this->valueType instanceof Mixed_) {
            return 'list';
        }

        return 'list<' . $this->valueType . '>';
    }
}
