<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class IntersectionTypeNode implements TypeNode
{
    use NodeAttributes;

    public array $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function __toString(): string
    {
        return '(' . \implode(' & ', \array_map(static function (TypeNode $type): string {
            if ($type instanceof NullableTypeNode) {
                return '(' . $type . ')';
            }

            return (string) $type;
        }, $this->types)) . ')';
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['types']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
