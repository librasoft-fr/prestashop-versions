<?php

namespace PsMcpServerDeps\Mcp\Schema\Result;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\ResultInterface;

class ListRootsResult implements ResultInterface
{
    public function __construct(
        public readonly array $roots,
        public readonly ?array $meta = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        $result = [
            'roots' => array_values($this->roots),
        ];

        if (null !== $this->meta) {
            $result['_meta'] = $this->meta;
        }

        return $result;
    }
}
