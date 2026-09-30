<?php


declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\ArrayKey;


final class KeyOf extends ArrayKey implements PseudoType
{
    
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function underlyingType(): Type
    {
        return new ArrayKey();
    }

    public function __toString(): string
    {
        return 'key-of<' . $this->type . '>';
    }
}
