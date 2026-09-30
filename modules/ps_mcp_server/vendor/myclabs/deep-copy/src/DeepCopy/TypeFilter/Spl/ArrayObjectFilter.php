<?php
namespace DeepCopy\TypeFilter\Spl;

use DeepCopy\DeepCopy;
use DeepCopy\TypeFilter\TypeFilter;


final class ArrayObjectFilter implements TypeFilter
{
    
    private $copier;

    public function __construct(DeepCopy $copier)
    {
        $this->copier = $copier;
    }

    
    public function apply($arrayObject)
    {
        $clone = clone $arrayObject;
        foreach ($arrayObject->getArrayCopy() as $k => $v) {
            $clone->offsetSet($k, $this->copier->copy($v));
        }

        return $clone;
    }
}

