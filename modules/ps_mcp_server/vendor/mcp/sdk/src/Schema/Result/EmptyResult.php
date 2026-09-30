<?php



namespace Mcp\Schema\Result;

use Mcp\Schema\JsonRpc\ResultInterface;


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
