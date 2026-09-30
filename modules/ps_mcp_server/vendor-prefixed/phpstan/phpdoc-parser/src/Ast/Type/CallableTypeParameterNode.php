<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Node;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class CallableTypeParameterNode implements Node
{
    use NodeAttributes;

    public TypeNode $type;

    public bool $isReference;

    public bool $isVariadic;

    public string $parameterName;

    public bool $isOptional;

    public function __construct(TypeNode $type, bool $isReference, bool $isVariadic, string $parameterName, bool $isOptional)
    {
        $this->type = $type;
        $this->isReference = $isReference;
        $this->isVariadic = $isVariadic;
        $this->parameterName = $parameterName;
        $this->isOptional = $isOptional;
    }

    public function __toString(): string
    {
        $type = "{$this->type} ";
        $isReference = $this->isReference ? '&' : '';
        $isVariadic = $this->isVariadic ? '...' : '';
        $isOptional = $this->isOptional ? '=' : '';

        return \trim("{$type}{$isReference}{$isVariadic}{$this->parameterName}") . $isOptional;
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['type'], $properties['isReference'], $properties['isVariadic'], $properties['parameterName'], $properties['isOptional']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
