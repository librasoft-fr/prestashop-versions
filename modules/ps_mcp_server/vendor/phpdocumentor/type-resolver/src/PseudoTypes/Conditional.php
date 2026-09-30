<?php


declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Mixed_;

use function sprintf;


final class Conditional extends Mixed_ implements PseudoType
{
    
    private $negated;
    
    private $subjectType;
    
    private $targetType;
    
    private $if;
    
    private $else;

    public function __construct(bool $negated, Type $subjectType, Type $targetType, Type $if, Type $else)
    {
        $this->negated = $negated;
        $this->subjectType = $subjectType;
        $this->targetType = $targetType;
        $this->if = $if;
        $this->else = $else;
    }

    public function isNegated(): bool
    {
        return $this->negated;
    }

    public function getSubjectType(): Type
    {
        return $this->subjectType;
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
        return sprintf(
            '(%s %s %s ? %s : %s)',
            (string) $this->subjectType,
            $this->negated ? 'is not' : 'is',
            (string) $this->targetType,
            (string) $this->if,
            (string) $this->else
        );
    }
}
