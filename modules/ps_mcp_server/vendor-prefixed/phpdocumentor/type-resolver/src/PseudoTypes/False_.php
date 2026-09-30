<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Boolean;

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

\class_alias(False_::class, 'PsMcpServerDeps\phpDocumentor\Reflection\Types\False_', false);
