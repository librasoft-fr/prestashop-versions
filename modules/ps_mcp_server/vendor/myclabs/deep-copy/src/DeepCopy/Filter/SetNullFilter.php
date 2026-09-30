<?php

namespace DeepCopy\Filter;

use DeepCopy\Reflection\ReflectionHelper;


class SetNullFilter implements Filter
{
    
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);

        if (PHP_VERSION_ID < 80100) {
            $reflectionProperty->setAccessible(true);
        }
        $reflectionProperty->setValue($object, null);
    }
}
