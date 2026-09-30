<?php

namespace PsMcpServerDeps\Mcp\Schema;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class PromptArgument implements \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?bool $required = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['name']) || !\is_string($data['name'])) {
            throw new InvalidArgumentException('Invalid or missing "name" in PromptArgument data.');
        }

        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            required: $data['required'] ?? null
        );
    }

    public function jsonSerialize(): array
    {
        $data = ['name' => $this->name];
        if (null !== $this->description) {
            $data['description'] = $this->description;
        }
        if (null !== $this->required) {
            $data['required'] = $this->required;
        }

        return $data;
    }
}
