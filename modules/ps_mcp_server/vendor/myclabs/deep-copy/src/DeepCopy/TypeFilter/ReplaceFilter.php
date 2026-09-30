<?php

namespace DeepCopy\TypeFilter;


class ReplaceFilter implements TypeFilter
{
    
    protected $callback;

    
    public function __construct(callable $callable)
    {
        $this->callback = $callable;
    }

    
    public function apply($element)
    {
        return call_user_func($this->callback, $element);
    }
}
