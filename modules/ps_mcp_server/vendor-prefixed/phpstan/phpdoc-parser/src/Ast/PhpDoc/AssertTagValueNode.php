<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\TypeNode;

class AssertTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public TypeNode $type;

    public string $parameter;

    public bool $isNegated;

    public bool $isEquality;

    public string $description;

    public function __construct(TypeNode $type, string $parameter, bool $isNegated, string $description, bool $isEquality)
    {
        $this->type = $type;
        $this->parameter = $parameter;
        $this->isNegated = $isNegated;
        $this->isEquality = $isEquality;
        $this->description = $description;
    }

    public function __toString(): string
    {
        $isNegated = $this->isNegated ? '!' : '';
        $isEquality = $this->isEquality ? '=' : '';

        return \trim("{$isNegated}{$isEquality}{$this->type} {$this->parameter} {$this->description}");
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['type'], $properties['parameter'], $properties['isNegated'], $properties['description'], $properties['isEquality']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
