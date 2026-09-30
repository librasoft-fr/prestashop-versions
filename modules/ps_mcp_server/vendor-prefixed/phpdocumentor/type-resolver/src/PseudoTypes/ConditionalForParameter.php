<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;

final class ConditionalForParameter extends Mixed_ implements PseudoType
{
    private $negated;

    private $parameterName;

    private $targetType;

    private $if;

    private $else;

    public function __construct(bool $negated, string $parameterName, Type $targetType, Type $if, Type $else)
    {
        $this->negated = $negated;
        $this->parameterName = $parameterName;
        $this->targetType = $targetType;
        $this->if = $if;
        $this->else = $else;
    }

    public function isNegated(): bool
    {
        return $this->negated;
    }

    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    public function getTargetType(): Type
    {
        return $this->targetType;
    }

    public function getIf(): Type
    {
        return $this->if;
    }

    public function getElse(): Type
    {
        return $this->else;
    }

    public function underlyingType(): Type
    {
        return new Mixed_();
    }

    public function __toString(): string
    {
        return \sprintf(
            '(%s %s %s ? %s : %s)',
            '$' . $this->parameterName,
            $this->negated ? 'is not' : 'is',
            (string) $this->targetType,
            (string) $this->if,
            (string) $this->else
        );
    }
}
