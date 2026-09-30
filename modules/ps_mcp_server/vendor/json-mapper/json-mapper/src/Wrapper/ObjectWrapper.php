<?php

declare(strict_types=1);

namespace JsonMapper\Wrapper;

use JsonMapper\Exception\TypeError;

class ObjectWrapper
{
    
    private $object;
    
    private $className;
    
    private $reflectedObject;

    
    public function __construct($object = null, ?string $className = null)
    {
        if (\is_null($object) && \is_null($className)) {
            throw new \BadFunctionCallException('Either object or className parameter must be provided, both are null');
        }
        if (! \is_null($object) && ! \is_object($object)) {
            throw TypeError::forArgument(__METHOD__, 'object', $object, 1, '$object');
        }
        if (! \is_null($className) && ! \class_exists($className)) {
            throw new \UnexpectedValueException(sprintf(
                'Argument 2 ($className) must be a valid class name, %s given',
                $className
            ));
        }

        $this->object = $object;
        $this->className = $className;
    }

    
    public function setObject($object): void
    {
        $this->object = $object;
        $this->reflectedObject = null;
    }

    
    public function getObject()
    {
        if (\is_null($this->object)) {
            $constructor = $this->getReflectedObject()->getConstructor();
            if (\is_null($constructor) || $constructor->getNumberOfParameters() === 0) {
                $this->object = $this->getReflectedObject()->newInstance();
            } else {
                $this->object = $this->getReflectedObject()->newInstanceWithoutConstructor();
            }
        }

        return $this->object;
    }

    
    public function getClassName(): ?string
    {
        return $this->className ;
    }

    public function getReflectedObject(): \ReflectionClass
    {
        if ($this->reflectedObject === null) {
            $objectOrClass = ! \is_null($this->object) ? $this->object : $this->className;
            $this->reflectedObject = new \ReflectionClass($objectOrClass);
        }

        return $this->reflectedObject;
    }

    public function getName(): string
    {
        return $this->getReflectedObject()->getName();
    }
}
