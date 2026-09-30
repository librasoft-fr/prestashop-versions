<?php

declare(strict_types=1);

namespace JsonMapper\Handler;

use JsonMapper\Enums\ScalarType;
use JsonMapper\Exception\ClassFactoryException;
use JsonMapper\Exception\TypeError;
use JsonMapper\Helpers\IScalarCaster;
use JsonMapper\JsonMapperInterface;
use JsonMapper\ValueObjects\Property;
use JsonMapper\ValueObjects\PropertyType;

class ValueFactory
{
    
    private $scalarCaster;
    
    private $classFactoryRegistry;
    
    private $nonInstantiableTypeResolver;

    public function __construct(
        IScalarCaster $scalarCaster,
        FactoryRegistry $classFactoryRegistry,
        FactoryRegistry $nonInstantiableTypeResolver
    ) {
        $this->scalarCaster = $scalarCaster;
        $this->classFactoryRegistry = $classFactoryRegistry;
        $this->nonInstantiableTypeResolver = $nonInstantiableTypeResolver;
    }

    
    public function build(JsonMapperInterface $mapper, Property $property, $value)
    {
        if (\is_null($value) && $property->isNullable()) {
            return null;
        }

        
        if (\count($property->getPropertyTypes()) > 1) {
            foreach ($property->getPropertyTypes() as $type) {
                if (\is_array($value) && $type->isArray() && count($value) === 0) {
                    return [];
                }

                if (\is_array($value) && $type->isArray()) {
                    $copy = $value;
                    $firstValue = \array_shift($copy);

                    
                    if ($this->propertyTypeAndValueTypeAreScalarAndSameType($type, $firstValue)) {
                        $scalarType = new ScalarType($type->getType());
                        return \array_map(function ($v) use ($scalarType) {
                            return $this->scalarCaster->cast($scalarType, $v);
                        }, $value);
                    }

                    if (PHP_VERSION_ID >= 80100 && enum_exists($type->getType())) {
                        return $this->mapToEnum($type, $value);
                    }

                    
                    if ($this->classFactoryRegistry->hasFactory($type->getType())) {
                        return $this->mapToObjectsUsingFactory($type, $value);
                    }

                    
                    if ((class_exists($type->getType()) || interface_exists($type->getType()))) {
                        return $this->mapToObjects($type, $value, $mapper);
                    }

                    continue;
                }

                
                if ($this->classFactoryRegistry->hasFactory($type->getType())) {
                    return $this->mapToObjectsUsingFactory($type, $value);
                }

                
                if ($this->propertyTypeAndValueTypeAreScalarAndSameType($type, $value)) {
                    return $this->scalarCaster->cast(new ScalarType($type->getType()), $value);
                }

                if (PHP_VERSION_ID >= 80100 && enum_exists($type->getType())) {
                    return $this->mapToEnum($type, $value);
                }

                
                if (\class_exists($type->getType())) {
                    return $this->mapToSingleObject($type->getType(), $value, $mapper);
                }
            }
        }

        
        $types = $property->getPropertyTypes();
        $type = \array_shift($types);

        if ($type === null) {
            
            return $value;
        }

        if (ScalarType::isValid($type->getType())) {
            return $this->mapToScalarValues($type, $value);
        }

        if (PHP_VERSION_ID >= 80100 && enum_exists($type->getType())) {
            return $this->mapToEnum($type, $value);
        }

        if ($this->classFactoryRegistry->hasFactory($type->getType())) {
            return $this->mapToObjectsUsingFactory($type, $value);
        }

        if ((class_exists($type->getType()) || interface_exists($type->getType()))) {
            return $this->mapToObjects($type, $value, $mapper);
        }

        throw new \Exception("Unable to map to {$type->getType()}");
    }

    
    private function propertyTypeAndValueTypeAreScalarAndSameType(PropertyType $type, $value): bool
    {
        if (! \is_scalar($value) || ! ScalarType::isValid($type->getType())) {
            return false;
        }

        $valueType = \gettype($value);
        if ($valueType === 'double') {
            $valueType = 'float';
        }

        return $type->getType() === $valueType;
    }

    private function mapToEnum(PropertyType $type, $value)
    {
        if ($type->isMultiDimensionalArray()) {
            return $this->recursiveMapToArrayOfEnum($type->getType(), $value);
        }
        if ($type->isArray()) {
            return $this->mapToArrayOfEnum($type->getType(), $value);
        }
        return $this->mapToSingleEnum($type->getType(), $value);
    }

    
    private function mapToArrayOfEnum(string $type, $value): array
    {
        return \array_map(function ($v) use ($type) {
            return $this->mapToSingleEnum($type, $v);
        }, (array) $value);
    }

    
    private function recursiveMapToArrayOfEnum(string $type, $value): array
    {
        return \array_map(function ($v) use ($type) {
            if (is_array($v)) {
                return $this->recursiveMapToArrayOfEnum($type, $v);
            }

            return $this->mapToSingleEnum($type, $v);
        }, (array) $value);
    }

    
    private function mapToSingleEnum(string $type, $value)
    {
        return call_user_func("{$type}::from", $value);
    }

    private function mapToScalarValues(PropertyType $type, $value)
    {
        if ($type->isMultiDimensionalArray()) {
            return $this->recursiveMapToArrayOfScalarValue($type->getType(), $value);
        }
        if ($type->isArray()) {
            return $this->mapToArrayOfScalarValue($type->getType(), $value);
        }
        return $this->mapToSingleScalarValue($type->getType(), $value);
    }

    
    private function mapToSingleScalarValue(string $type, $value)
    {
        $scalar = new ScalarType($type);

        return $this->scalarCaster->cast($scalar, $value);
    }

    
    private function mapToArrayOfScalarValue(string $type, $value): array
    {
        $scalar = new ScalarType($type);
        return \array_map(function ($v) use ($type, $scalar) {
            return $this->scalarCaster->cast($scalar, $v);
        }, (array) $value);
    }

    
    private function recursiveMapToArrayOfScalarValue(string $type, $value): array
    {
        $scalar = new ScalarType($type);
        return \array_map(function ($v) use ($type, $scalar) {
            if (is_array($v)) {
                return $this->recursiveMapToArrayOfScalarValue($type, $v);
            }

            return $this->scalarCaster->cast($scalar, $v);
        }, (array) $value);
    }

    private function mapToObjectsUsingFactory(PropertyType $type, $value)
    {
        if ($type->isMultiDimensionalArray()) {
            return $this->recursiveMapToArrayOfObjectsUsingFactory($type->getType(), $value);
        }
        if ($type->isArray()) {
            return $this->mapToArrayOfObjectsUsingFactory($type->getType(), $value);
        }
        return $this->mapToSingleObjectUsingFactory($type->getType(), $value);
    }

    
    private function mapToSingleObjectUsingFactory(string $type, $value)
    {
        return $this->classFactoryRegistry->create($type, $value);
    }

    private function mapToArrayOfObjectsUsingFactory(string $type, $value): array
    {
        return \array_map(function ($v) use ($type) {
            return $this->mapToSingleObjectUsingFactory($type, $v);
        }, (array) $value);
    }

    private function recursiveMapToArrayOfObjectsUsingFactory(string $type, $value): array
    {
        return \array_map(function ($v) use ($type) {
            if (is_array($v)) {
                return $this->recursiveMapToArrayOfObjectsUsingFactory($type, $v);
            }

            return $this->mapToSingleObjectUsingFactory($type, $v);
        }, (array) $value);
    }

    private function mapToObjects(PropertyType $type, $value, JsonMapperInterface $mapper)
    {
        if ($type->isMultiDimensionalArray()) {
            return $this->recursiveMapToArrayOfObjects($type->getType(), $value, $mapper);
        }
        if ($type->isArray()) {
            return $this->mapToArrayOfObjects($type->getType(), $value, $mapper);
        }
        return $this->mapToSingleObject($type->getType(), $value, $mapper);
    }

    
    private function recursiveMapToArrayOfObjects(string $type, $value, JsonMapperInterface $mapper): array
    {
        return \array_map(function ($v) use ($type, $mapper) {
            if (is_array($v)) {
                return $this->recursiveMapToArrayOfObjects($type, $v, $mapper);
            }

            return $this->mapToSingleObject($type, $v, $mapper);
        }, (array) $value);
    }

    
    private function mapToArrayOfObjects(string $type, $value, JsonMapperInterface $mapper): array
    {
        return \array_map(function ($v) use ($type, $mapper) {
            return $this->mapToSingleObject($type, $v, $mapper);
        }, (array) $value);
    }

    
    private function mapToSingleObject(string $type, $value, JsonMapperInterface $mapper)
    {
        $reflectionType = new \ReflectionClass($type);
        if (!$reflectionType->isInstantiable()) {
            return $this->resolveUnInstantiableType($type, $value, $mapper);
        }

        return $mapper->mapToClass($value, $type);
    }

    
    private function resolveUnInstantiableType(string $type, $value, JsonMapperInterface $mapper)
    {
        try {
            $instance = $this->nonInstantiableTypeResolver->create($type, $value);
            $mapper->mapObject($value, $instance);
            return $instance;
        } catch (ClassFactoryException $e) {
            throw new \RuntimeException(
                "Unable to resolve un-instantiable {$type} as no factory was registered",
                0,
                $e
            );
        }
    }
}
