<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection;

interface Element
{
    public function getFqsen(): Fqsen;

    public function getName(): string;
}
