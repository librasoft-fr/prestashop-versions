<?php

namespace DeepCopy\TypeFilter;


class ShallowCopyFilter implements TypeFilter
{
    
    public function apply($element)
    {
        return clone $element;
    }
}
