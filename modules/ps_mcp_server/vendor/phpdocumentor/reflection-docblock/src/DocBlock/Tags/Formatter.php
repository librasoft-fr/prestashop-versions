<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Tag;

interface Formatter
{
    
    public function format(Tag $tag): string;
}
