<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\Factory;

interface TagFactory extends Factory
{
    
    public function addParameter(string $name, $value): void;

    
    public function addService(object $service): void;

    
    public function registerTagHandler(string $tagName, $handler): void;
}
