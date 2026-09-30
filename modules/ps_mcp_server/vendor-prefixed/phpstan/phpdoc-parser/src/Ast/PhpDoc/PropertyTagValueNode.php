<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\TypeNode;

class PropertyTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public TypeNode $type;

    public string $propertyName;

    public string $description;

    public function __construct(TypeNode $type, string $propertyName, string $description)
    {
        $this->type = $type;
        $this->propertyName = $propertyName;
        $this->description = $description;
    }

    public function __toString(): string
    {
        return \trim("{$this->type} {$this->propertyName} {$this->description}");
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['type'], $properties['propertyName'], $properties['description']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
