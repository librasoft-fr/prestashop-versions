<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\TypeNode;

class TypeAliasTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public string $alias;

    public TypeNode $type;

    public function __construct(string $alias, TypeNode $type)
    {
        $this->alias = $alias;
        $this->type = $type;
    }

    public function __toString(): string
    {
        return \trim("{$this->alias} {$this->type}");
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['alias'], $properties['type']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
