<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Node;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ArrayShapeItemNode implements Node
{
    use NodeAttributes;

    public $keyName;

    public bool $optional;

    public TypeNode $valueType;

    public function __construct($keyName, bool $optional, TypeNode $valueType)
    {
        $this->keyName = $keyName;
        $this->optional = $optional;
        $this->valueType = $valueType;
    }

    public function __toString(): string
    {
        if ($this->keyName !== null) {
            return \sprintf(
                '%s%s: %s',
                (string) $this->keyName,
                $this->optional ? '?' : '',
                (string) $this->valueType,
            );
        }

        return (string) $this->valueType;
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['keyName'], $properties['optional'], $properties['valueType']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
