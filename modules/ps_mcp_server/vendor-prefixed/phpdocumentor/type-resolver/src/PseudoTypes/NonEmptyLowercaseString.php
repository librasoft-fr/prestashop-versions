<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\String_;

final class NonEmptyLowercaseString extends String_ implements PseudoType
{
    public function underlyingType(): Type
    {
        return new String_();
    }

    public function __toString(): string
    {
        return 'non-empty-lowercase-string';
    }
}
