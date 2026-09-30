<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;

final class ValueOf extends Mixed_ implements PseudoType
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
        return new Mixed_();
    }

    public function __toString(): string
    {
        return 'value-of<' . $this->type . '>';
    }
}
