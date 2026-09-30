<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Array_;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\ArrayKey;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;

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
        return 'array{' . \implode(', ', $this->items) . '}';
    }
}
