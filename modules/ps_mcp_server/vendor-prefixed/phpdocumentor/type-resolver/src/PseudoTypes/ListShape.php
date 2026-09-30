<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\PseudoTypes;

final class ListShape extends ArrayShape
{
    public function __toString(): string
    {
        return 'list{' . \implode(', ', $this->getItems()) . '}';
    }
}
