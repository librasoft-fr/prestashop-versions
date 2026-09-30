<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Reference;

use PsMcpServerDeps\Webmozart\Assert\Assert;

final class Url implements Reference
{
    private string $uri;

    public function __construct(string $uri)
    {
        Assert::stringNotEmpty($uri);
        $this->uri = $uri;
    }

    public function __toString(): string
    {
        return $this->uri;
    }
}
