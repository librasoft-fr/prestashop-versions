<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Integer;

final class PositiveInteger extends Integer implements PseudoType
{
    public function underlyingType(): Type
    {
        return new Integer();
    }

    public function __toString(): string
    {
        return 'positive-int';
    }
}
