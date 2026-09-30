<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Formatter;

class PassthroughFormatter implements Formatter
{
    public function format(Tag $tag): string
    {
        return \trim('@' . $tag->getName() . ' ' . $tag);
    }
}
