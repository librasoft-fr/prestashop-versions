<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\ConstExpr;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;

class DoctrineConstExprStringNode extends ConstExprStringNode
{
    use NodeAttributes;

    public string $value;

    public function __construct(string $value)
    {
        parent::__construct($value, self::DOUBLE_QUOTED);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return self::escape($this->value);
    }

    public static function unescape(string $value): string
    {
        return \str_replace('""', '"', \substr($value, 1, \strlen($value) - 2));
    }

    private static function escape(string $value): string
    {
        return \sprintf('"%s"', \str_replace('"', '""', $value));
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
