<?php


namespace Opis\JsonSchema\Variables;

use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Variables;

final class RefVariablesContainer implements Variables
{

    private JsonPointer $pointer;

    private ?Variables $each;

    private bool $hasDefault;

    
    private $defaultValue;

    
    public function __construct(JsonPointer $pointer, ?Variables $each = null, $default = null)
    {
        $this->pointer = $pointer;
        $this->each = $each;
        $this->hasDefault = func_num_args() === 3;
        $this->defaultValue = $default;
    }

    
    public function pointer(): JsonPointer
    {
        return $this->pointer;
    }

    
    public function each(): ?Variables
    {
        return $this->each;
    }

    
    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    
    public function resolve($data, array $path = [])
    {
        $resolved = $this->pointer->data($data, $path, $this);
        if ($resolved === $this) {
            return $this->defaultValue;
        }

        if ($this->each && (is_array($resolved) || is_object($resolved))) {
            $path = $this->pointer->absolutePath($path);
            foreach ($resolved as $key => &$value) {
                $path[] = $key;
                $value = $this->each->resolve($data, $path);
                array_pop($path);
                unset($value);
            }
        }

        return $resolved;
    }
}