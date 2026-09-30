<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Types;

use PsMcpServerDeps\phpDocumentor\Reflection\Fqsen;
use PsMcpServerDeps\phpDocumentor\Reflection\PseudoType;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;

final class ClassString extends String_ implements PseudoType
{
    private $fqsen;

    public function __construct(?Fqsen $fqsen = null)
    {
        $this->fqsen = $fqsen;
    }

    public function underlyingType(): Type
    {
        return new String_();
    }

    public function getFqsen(): ?Fqsen
    {
        return $this->fqsen;
    }

    public function __toString(): string
    {
        if ($this->fqsen === null) {
            return 'class-string';
        }

        return 'class-string<' . (string) $this->fqsen . '>';
    }
}
