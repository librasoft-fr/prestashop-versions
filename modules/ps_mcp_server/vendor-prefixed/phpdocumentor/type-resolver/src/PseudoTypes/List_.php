<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Array_;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Integer;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;

final class List_ extends Array_ implements PseudoType
{
    public function underlyingType(): Type
    {
        return new Array_();
    }

    public function __construct(?Type $valueType = null)
    {
        parent::__construct($valueType, new Integer());
    }

    public function __toString(): string
    {
        if ($this->valueType instanceof Mixed_) {
            return 'list';
        }

        return 'list<' . $this->valueType . '>';
    }
}
