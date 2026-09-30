<?php

namespace DeepCopy;

use function function_exists;

if (false === function_exists('DeepCopy\deep_copy')) {
    
    function deep_copy($value, $useCloneMethod = false)
    {
        return (new DeepCopy($useCloneMethod))->copy($value);
    }
}
