<?php



namespace Symfony\Component\Finder\Iterator;

use Symfony\Component\Finder\Glob;


class FilenameFilterIterator extends MultiplePcreFilterIterator
{
    
    public function accept(): bool
    {
        return $this->isAccepted($this->current()->getFilename());
    }

    
    protected function toRegex(string $str): string
    {
        return $this->isRegex($str) ? $str : Glob::toRegex($str);
    }
}
