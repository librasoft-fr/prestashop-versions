<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;


class ArrayKey extends AggregatedType implements PseudoType
{
    public function __construct()
    {
        parent::__construct([new String_(), new Integer()], '|');
    }

    public function underlyingType(): Type
    {
        return new Compound([new String_(), new Integer()]);
    }

    public function __toString(): string
    {
        return 'array-key';
    }
}
