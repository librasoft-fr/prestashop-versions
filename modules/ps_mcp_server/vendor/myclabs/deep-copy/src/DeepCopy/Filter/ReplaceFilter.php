<?php

namespace DeepCopy\Filter;

use DeepCopy\Reflection\ReflectionHelper;


class ReplaceFilter implements Filter
{
    
    protected $callback;

    
    public function __construct(callable $callable)
    {
        $this->callback = $callable;
    }

    
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        if (PHP_VERSION_ID < 80100) {
            $reflectionProperty->setAccessible(true);
        }

        $value = call_user_func($this->callback, $reflectionProperty->getValue($object));

        $reflectionProperty->setValue($object, $value);
    }
}
