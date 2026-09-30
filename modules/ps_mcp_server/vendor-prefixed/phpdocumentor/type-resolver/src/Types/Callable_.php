<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Types;

use PsMcpServerDeps\phpDocumentor\Reflection\Type;

final class Callable_ implements Type
{
    private $returnType;

    private $parameters;

    public function __construct(array $parameters = [], ?Type $returnType = null)
    {
        $this->parameters = $parameters;
        $this->returnType = $returnType;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getReturnType(): ?Type
    {
        return $this->returnType;
    }

    public function __toString(): string
    {
        return 'callable';
    }
}
