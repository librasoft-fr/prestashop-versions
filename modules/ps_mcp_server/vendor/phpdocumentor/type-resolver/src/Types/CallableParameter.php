<?php


declare(strict_types=1);

namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class CallableParameter
{
    
    private $type;

    
    private $isReference;

    
    private $isVariadic;

    
    private $isOptional;

    
    private $name;

    public function __construct(
        Type $type,
        ?string $name = null,
        bool $isReference = false,
        bool $isVariadic = false,
        bool $isOptional = false
    ) {
        $this->type = $type;
        $this->isReference = $isReference;
        $this->isVariadic = $isVariadic;
        $this->isOptional = $isOptional;
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function isReference(): bool
    {
        return $this->isReference;
    }

    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }
}
