<?php

namespace DeepCopy\Matcher;


class PropertyNameMatcher implements Matcher
{
    
    private $property;

    
    public function __construct($property)
    {
        $this->property = $property;
    }

    
    public function matches($object, $property)
    {
        return $property == $this->property;
    }
}
