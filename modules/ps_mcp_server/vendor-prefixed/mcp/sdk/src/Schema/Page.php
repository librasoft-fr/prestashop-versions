<?php

namespace PsMcpServerDeps\Mcp\Schema;

final class Page extends \ArrayObject
{
    public function __construct(
        public readonly array $references,
        public readonly ?string $nextCursor,
    ) {
        parent::__construct($references, \ArrayObject::ARRAY_AS_PROPS);
    }

    public function count(): int
    {
        return \count($this->references);
    }
}
