<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;

use function implode;


final class ObjectShape implements PseudoType
{
    
    private $items;

    public function __construct(ObjectShapeItem ...$items)
    {
        $this->items = $items;
    }

    
    public function getItems(): array
    {
        return $this->items;
    }

    public function underlyingType(): Type
    {
        return new Object_();
    }

    public function __toString(): string
    {
        return 'object{' . implode(', ', $this->items) . '}';
    }
}
