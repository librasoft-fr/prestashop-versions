<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Types;

use PsMcpServerDeps\phpDocumentor\Reflection\Fqsen;
use PsMcpServerDeps\phpDocumentor\Reflection\Type;

final class Object_ implements Type
{
    private $fqsen;

    public function __construct(?Fqsen $fqsen = null)
    {
        if (\strpos((string) $fqsen, '::') !== false || \strpos((string) $fqsen, '()') !== false) {
            throw new \InvalidArgumentException('Object types can only refer to a class, interface or trait but a method, function, constant or property was received: ' . (string) $fqsen);
        }

        $this->fqsen = $fqsen;
    }

    public function getFqsen(): ?Fqsen
    {
        return $this->fqsen;
    }

    public function __toString(): string
    {
        if ($this->fqsen) {
            return (string) $this->fqsen;
        }

        return 'object';
    }
}
