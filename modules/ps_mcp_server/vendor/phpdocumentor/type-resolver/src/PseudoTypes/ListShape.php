<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use function implode;


final class ListShape extends ArrayShape
{
    public function __toString(): string
    {
        return 'list{' . implode(', ', $this->getItems()) . '}';
    }
}
