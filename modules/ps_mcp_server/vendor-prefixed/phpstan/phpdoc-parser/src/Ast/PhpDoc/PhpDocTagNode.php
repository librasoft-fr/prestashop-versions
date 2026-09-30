<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineTagValueNode;

class PhpDocTagNode implements PhpDocChildNode
{
    use NodeAttributes;

    public string $name;

    public PhpDocTagValueNode $value;

    public function __construct(string $name, PhpDocTagValueNode $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function __toString(): string
    {
        if ($this->value instanceof DoctrineTagValueNode) {
            return (string) $this->value;
        }

        return \trim("{$this->name} {$this->value}");
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['name'], $properties['value']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
