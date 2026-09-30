<?php declare(strict_types=1);

namespace SebastianBergmann\CodeUnit;


final class FunctionUnit extends CodeUnit
{
    
    public function isFunction(): bool
    {
        return true;
    }
}
