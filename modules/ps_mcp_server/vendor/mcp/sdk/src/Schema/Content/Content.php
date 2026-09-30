<?php



namespace Mcp\Schema\Content;


abstract class Content implements \JsonSerializable
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
