<?php

namespace DeepCopy\Matcher;

interface Matcher
{
    
    public function matches($object, $property);
}
