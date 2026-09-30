<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Reference;

use PsMcpServerDeps\phpDocumentor\Reflection\Fqsen as RealFqsen;

final class Fqsen implements Reference
{
    private RealFqsen $fqsen;

    public function __construct(RealFqsen $fqsen)
    {
        $this->fqsen = $fqsen;
    }

    public function __toString(): string
    {
        return (string) $this->fqsen;
    }
}
