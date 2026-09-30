<?php

declare(strict_types=1);

namespace MabeEnum;

use ReflectionClass;
use InvalidArgumentException;
use LogicException;


abstract class Enum implements \Stringable
{
    
    private $value;

    
    private $ordinal;

    
    private static $constants = [];

    
    private static $names = [];

    
    private static $instances = [];

    
    final private function __construct($value, $ordinal = null)
    {
        $this->value   = $value;
        $this->ordinal = $ordinal;
    }

    
    public function __toString(): string
    {
        return $this->getName();
    }

    
    final public function __clone()
    {
        throw new LogicException('Enums are not cloneable');
    }

    
    final public function __sleep()
    {
        throw new LogicException('Serialization is not supported by default in this pseudo-enum implementation');
    }

    
    public function __serialize(): array
    {
        throw new LogicException('Serialization is not supported by default in this pseudo-enum implementation');
    }

    
    final public function __wakeup()
    {
        throw new LogicException('Serialization is not supported by default in this pseudo-enum implementation');
    }

    
    public function __unserialize(array $data): void
    {
        throw new LogicException('Serialization is not supported by default in this pseudo-enum implementation');
    }
    
    
    final public function getValue()
    {
        return $this->value;
    }

    
    final public function getName()
    {
        return self::$names[static::class][$this->ordinal ?? $this->getOrdinal()];
    }

    
    final public function getOrdinal()
    {
        if ($this->ordinal === null) {
            $ordinal   = 0;
            $value     = $this->value;
            $constants = self::$constants[static::class] ?? static::getConstants();
            foreach ($constants as $constValue) {
                if ($value === $constValue) {
                    break;
                }
                ++$ordinal;
            }

            $this->ordinal = $ordinal;
        }

        return $this->ordinal;
    }

    
    final public function is($enumerator)
    {
        return $this === $enumerator || $this->value === $enumerator

            
            || ($enumerator instanceof static
                && \get_class($enumerator) === static::class
                && $enumerator->value === $this->value
            );
    }

    
    final public static function get($enumerator)
    {
        if ($enumerator instanceof static) {
            if (\get_class($enumerator) !== static::class) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value of type %s for enumeration %s',
                    \get_class($enumerator),
                    static::class
                ));
            }

            return $enumerator;
        }

        return static::byValue($enumerator);
    }

    
    final public static function byValue($value)
    {
        

        $constants = self::$constants[static::class] ?? static::getConstants();

        $name = \array_search($value, $constants, true);
        if ($name === false) {
            throw new InvalidArgumentException(sprintf(
                'Unknown value %s for enumeration %s',
                \is_scalar($value)
                    ? \var_export($value, true)
                    : 'of type ' . (\is_object($value) ? \get_class($value) : \gettype($value)),
                static::class
            ));
        }

        
        $instance = self::$instances[static::class][$name]
            ?? self::$instances[static::class][$name] = new static($constants[$name]);

        return $instance;
    }

    
    final public static function byName(string $name)
    {
        if (isset(self::$instances[static::class][$name])) {
            
            $instance = self::$instances[static::class][$name];
            return $instance;
        }

        $const = static::class . "::{$name}";
        if (!\defined($const)) {
            throw new InvalidArgumentException("{$const} not defined");
        }

        assert(
            self::noAmbiguousValues(static::getConstants()),
            'Ambiguous enumerator values detected for ' . static::class
        );

        
        $value = \constant($const);
        return self::$instances[static::class][$name] = new static($value);
    }

    
    final public static function byOrdinal(int $ordinal)
    {
        $constants = self::$constants[static::class] ?? static::getConstants();

        if (!isset(self::$names[static::class][$ordinal])) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid ordinal number %s, must between 0 and %s',
                $ordinal,
                \count(self::$names[static::class]) - 1
            ));
        }

        $name = self::$names[static::class][$ordinal];

        
        $instance = self::$instances[static::class][$name]
            ?? self::$instances[static::class][$name] = new static($constants[$name], $ordinal);

        return $instance;
    }

    
    final public static function getEnumerators()
    {
        if (!isset(self::$names[static::class])) {
            static::getConstants();
        }

        
        $byNameFn = [static::class, 'byName'];
        return \array_map($byNameFn, self::$names[static::class]);
    }

    
    final public static function getValues()
    {
        return \array_values(self::$constants[static::class] ?? static::getConstants());
    }

    
    final public static function getNames()
    {
        if (!isset(self::$names[static::class])) {
            static::getConstants();
        }
        return self::$names[static::class];
    }

    
    final public static function getOrdinals()
    {
        $count = \count(self::$constants[static::class] ?? static::getConstants());
        return $count ? \range(0, $count - 1) : [];
    }

    
    final public static function getConstants()
    {
        if (isset(self::$constants[static::class])) {
            return self::$constants[static::class];
        }

        $reflection = new ReflectionClass(static::class);
        $constants  = [];

        do {
            $scopeConstants = [];
            
            foreach ($reflection->getReflectionConstants() as $reflConstant) {
                if ($reflConstant->isPublic()) {
                    $scopeConstants[ $reflConstant->getName() ] = $reflConstant->getValue();
                }
            }

            $constants = $scopeConstants + $constants;
        } while (($reflection = $reflection->getParentClass()) && $reflection->name !== __CLASS__);

        

        assert(
            self::noAmbiguousValues($constants),
            'Ambiguous enumerator values detected for ' . static::class
        );

        self::$names[static::class] = \array_keys($constants);
        return self::$constants[static::class] = $constants;
    }

    
    private static function noAmbiguousValues($constants)
    {
        foreach ($constants as $value) {
            $names = \array_keys($constants, $value, true);
            if (\count($names) > 1) {
                return false;
            }
        }

        return true;
    }

    
    final public static function has($enumerator)
    {
        if ($enumerator instanceof static) {
            return \get_class($enumerator) === static::class;
        }

        return static::hasValue($enumerator);
    }

    
    final public static function hasValue($value)
    {
        return \in_array($value, self::$constants[static::class] ?? static::getConstants(), true);
    }

    
    final public static function hasName(string $name)
    {
        return \defined("static::{$name}");
    }

    
    final public static function __callStatic(string $method, array $args)
    {
        return static::byName($method);
    }
}
