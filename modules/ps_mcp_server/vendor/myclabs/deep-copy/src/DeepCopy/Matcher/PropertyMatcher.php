<?php

namespace DeepCopy\Matcher;


class PropertyMatcher implements Matcher
{
    
    private $class;

    
    private $property;

    
    public function __construct($class, $property)
    {
        $this->class = $class;
        $this->property = $property;
    }

    
    public function matches($object, $property)
    {
        return ($object instanceof $this->class) && $property == $this->property;
    }
}
