<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use function trim;

class PassthroughFormatter implements Formatter
{
    
    public function format(Tag $tag): string
    {
        return trim('@' . $tag->getName() . ' ' . $tag);
    }
}
