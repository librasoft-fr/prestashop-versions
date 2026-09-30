<?php declare(strict_types=1);

namespace SebastianBergmann\CodeUnit;


final class FileUnit extends CodeUnit
{
    
    public function isFile(): bool
    {
        return true;
    }
}
