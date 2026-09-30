<?php

declare(strict_types=1);

namespace MabeEnum;

use RuntimeException;
use LogicException;


trait EnumSerializableTrait
{
    
    abstract public function getValue();

    
    public function __serialize(): array
    {
        return ['value' => $this->getValue()];
    }

    
    public function __unserialize(array $data): void
    {
        if (!\array_key_exists('value', $data)) {
            throw new RuntimeException('Missing array key "value"');
        }

        
        $value     = $data['value'];
        $constants = self::getConstants();
        $name      = \array_search($value, $constants, true);
        if ($name === false) {
            $message = \is_scalar($value)
                ? 'Unknown value ' . \var_export($value, true)
                : 'Invalid value of type ' . (\is_object($value) ? \get_class($value) : \gettype($value));
            throw new RuntimeException($message);
        }

        $class      = static::class;
        $enumerator = $this;
        $closure    = function () use ($class, $name, $value, $enumerator) {
            if ($value !== null && $this->value !== null) {
                throw new LogicException('Do not call this directly - please use unserialize($enum) instead');
            }

            $this->value = $value;

            if (!isset(self::$instances[$class][$name])) {
                self::$instances[$class][$name] = $enumerator;
            }
        };
        $closure->bindTo($this, Enum::class)();
    }

    
    public function serialize(): string
    {
        return \serialize($this->getValue());
    }

    
    public function unserialize($serialized): void
    {
        $this->__unserialize(['value' => \unserialize($serialized)]);
    }
}
