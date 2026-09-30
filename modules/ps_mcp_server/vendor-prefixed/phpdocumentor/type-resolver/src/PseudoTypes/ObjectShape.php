<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Object_;

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
        return 'object{' . \implode(', ', $this->items) . '}';
    }
}
