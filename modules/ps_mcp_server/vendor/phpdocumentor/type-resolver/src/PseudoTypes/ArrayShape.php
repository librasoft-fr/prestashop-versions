<?php


declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ArrayKey;
use phpDocumentor\Reflection\Types\Mixed_;

use function implode;


class ArrayShape implements PseudoType
{
    
    private $items;

    public function __construct(ArrayShapeItem ...$items)
    {
        $this->items = $items;
    }

    
    public function getItems(): array
    {
        return $this->items;
    }

    public function underlyingType(): Type
    {
        return new Array_(new Mixed_(), new ArrayKey());
    }

    public function __toString(): string
    {
        return 'array{' . implode(', ', $this->items) . '}';
    }
}
