<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context as TypeContext;

interface Factory
{
    public function create(string $tagLine, ?TypeContext $context = null): Tag;
}
