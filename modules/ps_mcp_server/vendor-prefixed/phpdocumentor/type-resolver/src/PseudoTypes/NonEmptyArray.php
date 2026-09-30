<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Array_;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;

final class NonEmptyArray extends Array_ implements PseudoType
{
    public function underlyingType(): Type
    {
        return new Array_($this->valueType, $this->keyType);
    }

    public function __toString(): string
    {
        if ($this->keyType) {
            return 'non-empty-array<' . $this->keyType . ',' . $this->valueType . '>';
        }

        if ($this->valueType instanceof Mixed_) {
            return 'non-empty-array';
        }

        return 'non-empty-array<' . $this->valueType . '>';
    }
}
