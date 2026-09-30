<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Node;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ArrayShapeUnsealedTypeNode implements Node
{
    use NodeAttributes;

    public TypeNode $valueType;

    public ?TypeNode $keyType = null;

    public function __construct(TypeNode $valueType, ?TypeNode $keyType)
    {
        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }

    public function __toString(): string
    {
        if ($this->keyType !== null) {
            return \sprintf('<%s, %s>', $this->keyType, $this->valueType);
        }

        return \sprintf('<%s>', $this->valueType);
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['valueType'], $properties['keyType']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
