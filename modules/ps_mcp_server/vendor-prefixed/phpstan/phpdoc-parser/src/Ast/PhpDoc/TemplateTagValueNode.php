<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type\TypeNode;

class TemplateTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public string $name;

    public ?TypeNode $bound;

    public ?TypeNode $default;

    public ?TypeNode $lowerBound;

    public string $description;

    public function __construct(string $name, ?TypeNode $bound, string $description, ?TypeNode $default = null, ?TypeNode $lowerBound = null)
    {
        $this->name = $name;
        $this->bound = $bound;
        $this->lowerBound = $lowerBound;
        $this->default = $default;
        $this->description = $description;
    }

    public function __toString(): string
    {
        $upperBound = $this->bound !== null ? " of {$this->bound}" : '';
        $lowerBound = $this->lowerBound !== null ? " super {$this->lowerBound}" : '';
        $default = $this->default !== null ? " = {$this->default}" : '';

        return \trim("{$this->name}{$upperBound}{$lowerBound}{$default} {$this->description}");
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['name'], $properties['bound'], $properties['description'], $properties['default'] ?? null, $properties['lowerBound'] ?? null);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
