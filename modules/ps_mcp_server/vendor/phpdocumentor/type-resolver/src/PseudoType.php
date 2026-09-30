<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection;

interface PseudoType extends Type
{
    public function underlyingType(): Type;
}
