<?php

namespace DeepCopy\Filter;


interface Filter
{
    
    public function apply($object, $property, $objectCopier);
}
