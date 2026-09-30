<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Formatter;

class AlignFormatter implements Formatter
{
    protected int $maxLen = 0;

    public function __construct(array $tags)
    {
        foreach ($tags as $tag) {
            $this->maxLen = \max($this->maxLen, \strlen($tag->getName()));
        }
    }

    public function format(Tag $tag): string
    {
        return '@' . $tag->getName() .
            \str_repeat(
                ' ',
                $this->maxLen - \strlen($tag->getName()) + 1
            ) .
            $tag;
    }
}
