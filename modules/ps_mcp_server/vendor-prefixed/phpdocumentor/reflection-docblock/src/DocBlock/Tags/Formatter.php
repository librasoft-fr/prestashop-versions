<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;

interface Formatter
{
    public function format(Tag $tag): string;
}
