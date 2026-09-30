<?php

declare(strict_types=1);



namespace Sentry\Serializer;

use Sentry\Options;


abstract class AbstractSerializer
{
    
    public const DEFAULT_MB_DETECT_ORDER = 'auto';

    
    public const WESTERN_MB_DETECT_ORDER = 'UTF-8, ASCII, ISO-8859-1, ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5, ISO-8859-6, ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10, ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16, Windows-1251, Windows-1252, Windows-1254';

    
    private $maxDepth;

    
    protected $mbDetectOrder = self::DEFAULT_MB_DETECT_ORDER;

    
    protected $serializeAllObjects = false;

    
    protected $options;

    
    public function __construct(Options $options, int $maxDepth = 3, ?string $mbDetectOrder = null)
    {
        $this->maxDepth = $maxDepth;

        if ($mbDetectOrder != null) {
            $this->mbDetectOrder = $mbDetectOrder;
        }

        $this->options = $options;
    }

    
    protected function serializeRecursively($value, int $_depth = 0)
    {
        try {
            if ($_depth >= $this->maxDepth) {
                return $this->serializeValue($value);
            }

            try {
                if (@\is_callable($value)) {
                    return $this->serializeCallable($value);
                }
            } catch (\Throwable $exception) {
                
            }

            if (\is_array($value)) {
                $serializedArray = [];

                foreach ($value as $k => $v) {
                    $serializedArray[$k] = $this->serializeRecursively($v, $_depth + 1);
                }

                return $serializedArray;
            }

            if (\is_object($value)) {
                $classSerializers = $this->resolveClassSerializers($value);

                
                foreach ($classSerializers as $classSerializer) {
                    try {
                        $serializedObjectData = $classSerializer($value);

                        if (\is_array($serializedObjectData)) {
                            return [
                                'class' => \get_class($value),
                                'data' => $this->serializeRecursively($serializedObjectData, $_depth + 1),
                            ];
                        }
                    } catch (\Throwable $e) {
                        
                    }
                }

                if ($value instanceof \DateTimeInterface) {
                    return $this->formatDate($value);
                }

                if ($value instanceof \UnitEnum) {
                    return $this->serializeValue($value);
                }

                if ($this->serializeAllObjects || ($value instanceof \stdClass)) {
                    return $this->serializeObject($value, $_depth);
                }
            }

            return $this->serializeValue($value);
        } catch (\Throwable $error) {
            if (\is_string($value)) {
                return $value . ' {serialization error}';
            }

            return '{serialization error}';
        }
    }

    
    protected function resolveClassSerializers($object): array
    {
        $serializers = [];

        foreach ($this->options->getClassSerializers() as $type => $serializer) {
            if ($object instanceof $type) {
                $serializers[] = $serializer;
            }
        }

        if ($object instanceof SerializableInterface) {
            $serializers[] = static function (SerializableInterface $object): ?array {
                return $object->toSentry();
            };
        }

        return $serializers;
    }

    
    protected function serializeObject($object, int $_depth = 0, array $hashes = [])
    {
        if ($_depth >= $this->maxDepth || \in_array(spl_object_hash($object), $hashes, true)) {
            return $this->serializeValue($object);
        }

        $hashes[] = spl_object_hash($object);
        $serializedObject = [];

        foreach ($object as $key => &$value) {
            if (\is_object($value)) {
                $serializedObject[$key] = $this->serializeObject($value, $_depth + 1, $hashes);
            } else {
                $serializedObject[$key] = $this->serializeRecursively($value, $_depth + 1);
            }
        }

        return $serializedObject;
    }

    
    protected function serializeString(string $value): string
    {
        if ($value === '') {
            return '';
        }

        
        if ($currentEncoding = mb_detect_encoding($value, $this->mbDetectOrder)) {
            $encoded = mb_convert_encoding($value, 'UTF-8', $currentEncoding) ?: '<encoding error>';
        } else {
            $encoded = mb_convert_encoding($value, 'UTF-8') ?: '<encoding error>';
        }

        if (mb_strlen($encoded) > $this->options->getMaxValueLength()) {
            $encoded = mb_substr($encoded, 0, $this->options->getMaxValueLength() - 10, 'UTF-8') . ' {clipped}';
        }

        return $encoded;
    }

    
    protected function serializeValue($value)
    {
        if (($value === null) || \is_bool($value) || is_numeric($value)) {
            return $value;
        }

        if ($value instanceof \UnitEnum) {
            $reflection = new \ReflectionObject($value);
            $enumValue = $reflection->getName() . '::' . $value->name;

            if ($value instanceof \BackedEnum) {
                return 'Enum ' . $enumValue . '(' . $value->value . ')';
            }

            return 'Enum ' . $enumValue;
        }

        if (\is_object($value)) {
            $reflection = new \ReflectionObject($value);

            $objectId = null;
            if ($reflection->hasProperty('id') && ($idProperty = $reflection->getProperty('id'))->isPublic()) {
                $objectId = $idProperty->getValue($value);
            } elseif ($reflection->hasMethod('getId') && ($getIdMethod = $reflection->getMethod('getId'))->isPublic()) {
                try {
                    $objectId = $getIdMethod->invoke($value);
                } catch (\Throwable $e) {
                    
                }
            }

            return 'Object ' . $reflection->getName() . (\is_scalar($objectId) ? '(#' . $objectId . ')' : '');
        }

        if (\is_resource($value)) {
            return 'Resource ' . get_resource_type($value);
        }

        try {
            if (@\is_callable($value)) {
                return $this->serializeCallable($value);
            }
        } catch (\Throwable $exception) {
            
        }

        if (\is_array($value)) {
            return 'Array of length ' . \count($value);
        }

        if (\is_string($value) || (\is_object($value) && method_exists($value, '__toString'))) {
            return $this->serializeString((string) $value);
        }

        return null;
    }

    private function formatDate(\DateTimeInterface $date): string
    {
        $hasMicroseconds = $date->format('u') !== '000000';
        $formatted = $hasMicroseconds ? $date->format('Y-m-d H:i:s.u') : $date->format('Y-m-d H:i:s');
        $className = \get_class($date);

        $timezone = $date->getTimezone();
        if ($timezone && $timezone->getName() !== 'UTC') {
            $formatted .= ' ' . $date->format('eP');
        }

        return "$className($formatted)";
    }

    
    protected function serializeCallable($callable): string
    {
        if (\is_string($callable)) {
            return $callable;
        }

        if (!\is_callable($callable)) {
            throw new \InvalidArgumentException(\sprintf('Expecting callable, got %s', \is_object($callable) ? \get_class($callable) : \gettype($callable)));
        }

        try {
            if (\is_array($callable)) {
                $reflection = new \ReflectionMethod($callable[0], $callable[1]);
                $class = $reflection->getDeclaringClass();
            } elseif ($callable instanceof \Closure) {
                $reflection = new \ReflectionFunction($callable);
                $class = null;
            } elseif (\is_object($callable) && method_exists($callable, '__invoke')) {
                $reflection = new \ReflectionMethod($callable, '__invoke');
                $class = $reflection->getDeclaringClass();
            } else {
                throw new \InvalidArgumentException('Unrecognized type of callable');
            }
        } catch (\ReflectionException $exception) {
            return '{unserializable callable, reflection error}';
        }

        $callableType = $reflection->isClosure() ? 'Lambda ' : 'Callable ';
        $callableReturnType = $reflection->getReturnType();

        if ($callableReturnType instanceof \ReflectionNamedType) {
            $callableType .= $callableReturnType->getName() . ' ';
        }

        if ($class) {
            $callableType .= $class->getName() . '::';
        }

        return $callableType . $reflection->getName() . ' ' . $this->serializeCallableParameters($reflection);
    }

    private function serializeCallableParameters(\ReflectionFunctionAbstract $reflection): string
    {
        $params = [];
        foreach ($reflection->getParameters() as &$param) {
            $reflectionType = $param->getType();
            if ($reflectionType instanceof \ReflectionNamedType) {
                $paramType = $reflectionType->getName();
            } else {
                $paramType = 'mixed';
            }

            if ($param->allowsNull()) {
                $paramType .= '|null';
            }

            $paramName = ($param->isPassedByReference() ? '&' : '') . $param->getName();

            if ($param->isOptional()) {
                $paramName = '[' . $paramName . ']';
            }

            $params[] = $paramType . ' ' . $paramName;
        }

        return '[' . implode('; ', $params) . ']';
    }

    public function getMbDetectOrder(): string
    {
        return $this->mbDetectOrder;
    }

    
    public function setMbDetectOrder(string $mbDetectOrder): self
    {
        $this->mbDetectOrder = $mbDetectOrder;

        return $this;
    }

    public function setSerializeAllObjects(bool $value): void
    {
        $this->serializeAllObjects = $value;
    }

    public function getSerializeAllObjects(): bool
    {
        return $this->serializeAllObjects;
    }
}
