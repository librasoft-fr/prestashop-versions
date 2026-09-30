<?php

namespace PsMcpServerDeps\Mcp\Schema;

class ModelHint implements \JsonSerializable
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        if (null === $this->name) {
            return [];
        }

        return ['name' => $this->name];
    }
}
