<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Types;

use PsMcpServerDeps\phpDocumentor\Reflection\Fqsen;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;

final class Collection extends AbstractList
{
    private $fqsen;

    public function __construct(?Fqsen $fqsen, Type $valueType, ?Type $keyType = null)
    {
        parent::__construct($valueType, $keyType);

        $this->fqsen = $fqsen;
    }

    public function getFqsen(): ?Fqsen
    {
        return $this->fqsen;
    }

    public function __toString(): string
    {
        $objectType = (string) ($this->fqsen ?? 'object');

        if ($this->keyType === null) {
            return $objectType . '<' . $this->valueType . '>';
        }

        return $objectType . '<' . $this->keyType . ',' . $this->valueType . '>';
    }
}
