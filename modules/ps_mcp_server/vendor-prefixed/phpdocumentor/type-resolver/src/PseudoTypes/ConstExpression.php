<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;

final class ConstExpression implements PseudoType
{
    private $owner;

    private $expression;

    public function __construct(Type $owner, string $expression)
    {
        $this->owner = $owner;
        $this->expression = $expression;
    }

    public function getOwner(): Type
    {
        return $this->owner;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function underlyingType(): Type
    {
        return new Mixed_();
    }

    public function __toString(): string
    {
        return \sprintf('%s::%s', (string) $this->owner, $this->expression);
    }
}
