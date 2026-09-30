<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection;

interface DocBlockFactoryInterface
{
    public static function createInstance(array $additionalTags = []): self;

    public function create($docblock, ?Types\Context $context = null, ?Location $location = null): DocBlock;
}
