<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ParamImmediatelyInvokedCallableTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public string $parameterName;

    public string $description;

    public function __construct(string $parameterName, string $description)
    {
        $this->parameterName = $parameterName;
        $this->description = $description;
    }

    public function __toString(): string
    {
        return \trim("{$this->parameterName} {$this->description}");
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['parameterName'], $properties['description']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
