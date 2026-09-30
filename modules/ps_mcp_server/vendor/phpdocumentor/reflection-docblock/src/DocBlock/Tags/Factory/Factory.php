<?php


declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\Types\Context as TypeContext;

interface Factory
{
    
    public function create(string $tagLine, ?TypeContext $context = null): Tag;
}
