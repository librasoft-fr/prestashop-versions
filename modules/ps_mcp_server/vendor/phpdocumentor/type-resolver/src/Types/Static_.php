<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;

use function implode;


final class Static_ implements Type
{
    
    private $genericTypes;

    public function __construct(Type ...$genericTypes)
    {
        $this->genericTypes = $genericTypes;
    }

    
    public function getGenericTypes(): array
    {
        return $this->genericTypes;
    }

    
    public function __toString(): string
    {
        if ($this->genericTypes) {
            return 'static<' . implode(', ', $this->genericTypes) . '>';
        }

        return 'static';
    }
}
