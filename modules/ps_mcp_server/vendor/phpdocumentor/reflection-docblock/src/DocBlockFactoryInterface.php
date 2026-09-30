<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Tag;


interface DocBlockFactoryInterface
{
    
    public static function createInstance(array $additionalTags = []): self;

    
    public function create($docblock, ?Types\Context $context = null, ?Location $location = null): DocBlock;
}
