<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use function max;
use function str_repeat;
use function strlen;

class AlignFormatter implements Formatter
{
    
    protected int $maxLen = 0;

    
    public function __construct(array $tags)
    {
        foreach ($tags as $tag) {
            $this->maxLen = max($this->maxLen, strlen($tag->getName()));
        }
    }

    
    public function format(Tag $tag): string
    {
        return '@' . $tag->getName() .
            str_repeat(
                ' ',
                $this->maxLen - strlen($tag->getName()) + 1
            ) .
            $tag;
    }
}
