<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Types;

use PsMcpServerDeps\phpDocumentor\Reflection\Type;

class Mixed_ implements Type
{
    public function __toString(): string
    {
        return 'mixed';
    }
}
