<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Integer;

final class IntMask extends Integer implements PseudoType
{
    private $types;

    public function __construct(Type ...$types)
    {
        $this->types = $types;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function underlyingType(): Type
    {
        return new Integer();
    }

    public function __toString(): string
    {
        return 'int-mask<' . \implode(', ', $this->types) . '>';
    }
}
