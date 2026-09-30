<?php

namespace DeepCopy\Matcher;

use DeepCopy\Reflection\ReflectionHelper;
use ReflectionException;


class PropertyTypeMatcher implements Matcher
{
    
    private $propertyType;

    
    public function __construct($propertyType)
    {
        $this->propertyType = $propertyType;
    }

    
    public function matches($object, $property)
    {
        try {
            $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        } catch (ReflectionException $exception) {
            return false;
        }

        if (PHP_VERSION_ID < 80100) {
            $reflectionProperty->setAccessible(true);
        }

        
        if (method_exists($reflectionProperty, 'isInitialized') && !$reflectionProperty->isInitialized($object)) {
            
            return false;
        }

        return $reflectionProperty->getValue($object) instanceof $this->propertyType;
    }
}
