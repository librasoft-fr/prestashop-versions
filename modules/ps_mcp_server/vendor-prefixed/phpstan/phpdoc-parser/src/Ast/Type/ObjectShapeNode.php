<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ObjectShapeNode implements TypeNode
{
    use NodeAttributes;

    public array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function __toString(): string
    {
        $items = $this->items;

        return 'object{' . \implode(', ', $items) . '}';
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['items']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
