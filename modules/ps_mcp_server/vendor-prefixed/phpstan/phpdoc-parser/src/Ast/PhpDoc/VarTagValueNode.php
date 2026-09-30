<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\TypeNode;

class VarTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public TypeNode $type;

    public string $variableName;

    public string $description;

    public function __construct(TypeNode $type, string $variableName, string $description)
    {
        $this->type = $type;
        $this->variableName = $variableName;
        $this->description = $description;
    }

    public function __toString(): string
    {
        return \trim("$this->type " . \trim("{$this->variableName} {$this->description}"));
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['type'], $properties['variableName'], $properties['description']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
