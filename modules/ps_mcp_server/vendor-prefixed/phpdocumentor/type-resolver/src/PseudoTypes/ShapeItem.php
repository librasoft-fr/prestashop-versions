<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

use PsMcpServerDeps\phpDocumentor\Reflection\Type;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Mixed_;

abstract class ShapeItem
{
    private $key;

    private $value;

    private $optional;

    public function __construct(?string $key, ?Type $value, bool $optional)
    {
        $this->key = $key;
        $this->value = $value ?? new Mixed_();
        $this->optional = $optional;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getValue(): Type
    {
        return $this->value;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function __toString(): string
    {
        if ($this->key !== null && $this->key !== '') {
            return \sprintf(
                '%s%s: %s',
                $this->key,
                $this->optional ? '?' : '',
                (string) $this->value
            );
        }

        return (string) $this->value;
    }
}
