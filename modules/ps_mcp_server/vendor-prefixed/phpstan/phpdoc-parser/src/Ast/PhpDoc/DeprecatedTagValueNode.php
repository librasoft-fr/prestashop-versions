<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class DeprecatedTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public string $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function __toString(): string
    {
        return \trim($this->description);
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['description']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
