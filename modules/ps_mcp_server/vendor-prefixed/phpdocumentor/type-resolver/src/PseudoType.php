<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection;

interface PseudoType extends Type
{
    public function underlyingType(): Type;
}
