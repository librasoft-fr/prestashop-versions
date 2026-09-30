<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;


final class Iterable_ extends AbstractList
{
    
    public function __toString(): string
    {
        if ($this->keyType) {
            return 'iterable<' . $this->keyType . ',' . $this->valueType . '>';
        }

        if ($this->valueType instanceof Mixed_) {
            return 'iterable';
        }

        return 'iterable<' . $this->valueType . '>';
    }
}
