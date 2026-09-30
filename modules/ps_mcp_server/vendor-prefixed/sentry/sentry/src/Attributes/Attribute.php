<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Attributes;

use PsMcpServerDeps\Sentry\Serializer\SerializableInterface;
use PsMcpServerDeps\Sentry\Util\JSON;

class Attribute
{
    private $type;

    private $value;

    public function __construct($value, string $type)
    {
        $this->value = $value;
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function fromValue($value): self
    {
        $attribute = self::tryFromValue($value);

        if ($attribute === null) {
            throw new \InvalidArgumentException(\sprintf('Invalid attribute value, %s cannot be serialized', \gettype($value)));
        }

        return $attribute;
    }

    public static function tryFromValue($value): ?self
    {
        if ($value === null) {
            return new self('null', 'string');
        }

        if (\is_bool($value)) {
            return new self($value, 'boolean');
        }

        if (\is_int($value)) {
            return new self($value, 'integer');
        }

        if (\is_float($value)) {
            return new self($value, 'double');
        }

        if ($value instanceof SerializableInterface) {
            try {
                return new self(JSON::encode($value->toSentry()), 'string');
            } catch (\Throwable $e) {
            }
        }

        if (\is_string($value) || (\is_object($value) && method_exists($value, '__toString'))) {
            return new self((string) $value, 'string');
        }

        try {
            return new self(JSON::encode($value), 'string');
        } catch (\Throwable $e) {
        }

        return null;
    }

    public function __toString(): string
    {
        return "{$this->value} ({$this->type})";
    }
}
