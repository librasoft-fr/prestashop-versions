<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class CallableTypeNode implements TypeNode
{
    use NodeAttributes;

    public IdentifierTypeNode $identifier;

    public array $templateTypes;

    public array $parameters;

    public TypeNode $returnType;

    public function __construct(IdentifierTypeNode $identifier, array $parameters, TypeNode $returnType, array $templateTypes)
    {
        $this->identifier = $identifier;
        $this->parameters = $parameters;
        $this->returnType = $returnType;
        $this->templateTypes = $templateTypes;
    }

    public function __toString(): string
    {
        $returnType = $this->returnType;
        if ($returnType instanceof self) {
            $returnType = "({$returnType})";
        }
        $template = $this->templateTypes !== []
            ? '<' . \implode(', ', $this->templateTypes) . '>'
            : '';
        $parameters = \implode(', ', $this->parameters);

        return "{$this->identifier}{$template}({$parameters}): {$returnType}";
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['identifier'], $properties['parameters'], $properties['returnType'], $properties['templateTypes']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
