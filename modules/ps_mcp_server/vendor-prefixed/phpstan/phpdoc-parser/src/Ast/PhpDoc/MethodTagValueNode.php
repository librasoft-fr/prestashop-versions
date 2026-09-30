<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\TypeNode;

class MethodTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public bool $isStatic;

    public ?TypeNode $returnType = null;

    public string $methodName;

    public array $templateTypes;

    public array $parameters;

    public string $description;

    public function __construct(bool $isStatic, ?TypeNode $returnType, string $methodName, array $parameters, string $description, array $templateTypes)
    {
        $this->isStatic = $isStatic;
        $this->returnType = $returnType;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
        $this->description = $description;
        $this->templateTypes = $templateTypes;
    }

    public function __toString(): string
    {
        $static = $this->isStatic ? 'static ' : '';
        $returnType = $this->returnType !== null ? "{$this->returnType} " : '';
        $parameters = \implode(', ', $this->parameters);
        $description = $this->description !== '' ? " {$this->description}" : '';
        $templateTypes = \count($this->templateTypes) > 0 ? '<' . \implode(', ', $this->templateTypes) . '>' : '';

        return "{$static}{$returnType}{$this->methodName}{$templateTypes}({$parameters}){$description}";
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['isStatic'], $properties['returnType'], $properties['methodName'], $properties['parameters'], $properties['description'], $properties['templateTypes']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
