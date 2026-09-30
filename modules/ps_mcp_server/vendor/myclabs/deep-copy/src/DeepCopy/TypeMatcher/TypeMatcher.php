<?php

namespace DeepCopy\TypeMatcher;

class TypeMatcher
{
    
    private $type;

    
    public function __construct($type)
    {
        $this->type = $type;
    }

    
    public function matches($element)
    {
        return is_object($element) ? is_a($element, $this->type) : gettype($element) === $this->type;
    }
}
