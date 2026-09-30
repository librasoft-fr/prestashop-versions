<?php

namespace PsMcpServerDeps\Mcp\Schema\JsonRpc;

interface HasMethodInterface
{
    public static function getMethod(): string;

    public static function fromArray(array $data): self;
}
