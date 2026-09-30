<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Integer;

final class IntMaskOf extends Integer implements PseudoType
{
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function underlyingType(): Type
    {
        return new Integer();
    }

    public function __toString(): string
    {
        return 'int-mask-of<' . $this->type . '>';
    }
}
