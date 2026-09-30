<?php

declare(strict_types=1);

namespace Sentry\Attributes;


class AttributeBag
{
    
    private $attributes = [];

    
    public function set(string $key, $value): self
    {
        $attribute = $value instanceof Attribute
            ? $value
            : Attribute::tryFromValue($value);

        if ($attribute !== null) {
            $this->attributes[$key] = $attribute;
        }

        return $this;
    }

    public function get(string $key): ?Attribute
    {
        return $this->attributes[$key] ?? null;
    }

    public function forget(string $key): self
    {
        unset($this->attributes[$key]);

        return $this;
    }

    
    public function all(): array
    {
        return $this->attributes;
    }

    
    public function toSimpleArray(): array
    {
        return array_map(static function (Attribute $attribute) {
            return $attribute->getValue();
        }, $this->attributes);
    }
}
