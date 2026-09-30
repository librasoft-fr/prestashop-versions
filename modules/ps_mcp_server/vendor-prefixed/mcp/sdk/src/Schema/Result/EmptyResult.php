<?php

namespace PsMcpServerDeps\Mcp\Schema\Result;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\ResultInterface;

class EmptyResult implements ResultInterface
{
    public function __construct()
    {
    }

    public static function fromArray(): self
    {
        return new self();
    }

    public function jsonSerialize(): object
    {
        return new \stdClass();
    }
}
