<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\ConstExpr;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ConstExprIntegerNode implements ConstExprNode
{
    use NodeAttributes;

    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['value']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
