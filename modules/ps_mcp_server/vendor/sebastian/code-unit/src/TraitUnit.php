<?php declare(strict_types=1);

namespace SebastianBergmann\CodeUnit;


final class TraitUnit extends CodeUnit
{
    
    public function isTrait(): bool
    {
        return true;
    }
}
