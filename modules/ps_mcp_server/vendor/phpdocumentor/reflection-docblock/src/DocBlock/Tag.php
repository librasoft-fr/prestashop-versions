<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock\Tags\Formatter;

interface Tag
{
    public function getName(): string;

    
    public static function create(string $body);

    public function render(?Formatter $formatter = null): string;

    public function __toString(): string;
}
