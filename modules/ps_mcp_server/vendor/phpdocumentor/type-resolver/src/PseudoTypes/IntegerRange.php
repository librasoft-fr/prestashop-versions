<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Integer;


final class IntegerRange extends Integer implements PseudoType
{
    
    private $minValue;

    
    private $maxValue;

    public function __construct(string $minValue, string $maxValue)
    {
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    public function underlyingType(): Type
    {
        return new Integer();
    }

    public function getMinValue(): string
    {
        return $this->minValue;
    }

    public function getMaxValue(): string
    {
        return $this->maxValue;
    }

    
    public function __toString(): string
    {
        return 'int<' . $this->minValue . ', ' . $this->maxValue . '>';
    }
}
