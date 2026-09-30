<?php declare(strict_types=1);

namespace SebastianBergmann\CodeUnit;


final class InterfaceMethodUnit extends CodeUnit
{
    
    public function isInterfaceMethod(): bool
    {
        return true;
    }
}
