<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory\Factory;

interface TagFactory extends Factory
{
    public function addParameter(string $name, $value): void;

    public function addService(object $service): void;

    public function registerTagHandler(string $tagName, $handler): void;
}
