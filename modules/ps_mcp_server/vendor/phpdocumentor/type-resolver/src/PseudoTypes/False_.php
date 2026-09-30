<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;

use function class_alias;


final class False_ extends Boolean implements PseudoType
{
    public function underlyingType(): Type
    {
        return new Boolean();
    }

    public function __toString(): string
    {
        return 'false';
    }
}

class_alias(False_::class, 'phpDocumentor\Reflection\Types\False_', false);
