<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

class DoctrineTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public DoctrineAnnotation $annotation;

    public string $description;

    public function __construct(
        DoctrineAnnotation $annotation,
        string $description,
    ) {
        $this->annotation = $annotation;
        $this->description = $description;
    }

    public function __toString(): string
    {
        return \trim("{$this->annotation} {$this->description}");
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['annotation'], $properties['description']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
