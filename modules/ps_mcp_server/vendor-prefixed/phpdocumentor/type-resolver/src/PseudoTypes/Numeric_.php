<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\AggregatedType;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Compound;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Float_;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Integer;

final class Numeric_ extends AggregatedType implements PseudoType
{
    public function __construct()
    {
        AggregatedType::__construct([new NumericString(), new Integer(), new Float_()], '|');
    }

    public function underlyingType(): Type
    {
        return new Compound([new NumericString(), new Integer(), new Float_()]);
    }

    public function __toString(): string
    {
        return 'numeric';
    }
}
