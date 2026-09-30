<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;


final class InterfaceString implements Type
{
    
    private $fqsen;

    
    public function __construct(?Fqsen $fqsen = null)
    {
        $this->fqsen = $fqsen;
    }

    
    public function getFqsen(): ?Fqsen
    {
        return $this->fqsen;
    }

    
    public function __toString(): string
    {
        if ($this->fqsen === null) {
            return 'interface-string';
        }

        return 'interface-string<' . (string) $this->fqsen . '>';
    }
}
