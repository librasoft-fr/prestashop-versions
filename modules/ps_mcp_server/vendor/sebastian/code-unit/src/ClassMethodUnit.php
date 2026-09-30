<?php declare(strict_types=1);

namespace SebastianBergmann\CodeUnit;


final class ClassMethodUnit extends CodeUnit
{
    
    public function isClassMethod(): bool
    {
        return true;
    }
}
