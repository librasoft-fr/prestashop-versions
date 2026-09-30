<?php

namespace PsMcpServerDeps\Mcp\Schema;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class Prompt implements \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?array $arguments = null,
        public readonly ?array $icons = null,
        public readonly ?array $meta = null,
    ) {
        if (null !== $this->arguments) {
            foreach ($this->arguments as $arg) {
                if (!$arg instanceof PromptArgument) {
                    throw new InvalidArgumentException('All items in Prompt "arguments" must be PromptArgument instances.');
                }
            }
        }
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['name']) || !\is_string($data['name'])) {
            throw new InvalidArgumentException('Invalid or missing "name" in Prompt data.');
        }
        $arguments = null;
        if (isset($data['arguments']) && \is_array($data['arguments'])) {
            $arguments = array_map(static fn (array $argData) => PromptArgument::fromArray($argData), $data['arguments']);
        }

        if (!empty($data['_meta']) && !\is_array($data['_meta'])) {
            throw new InvalidArgumentException('Invalid "_meta" in Prompt data.');
        }

        return new self(
            name: $data['name'],
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            arguments: $arguments,
            icons: isset($data['icons']) && \is_array($data['icons']) ? array_map(Icon::fromArray(...), $data['icons']) : null,
            meta: isset($data['_meta']) ? $data['_meta'] : null
        );
    }

    public function jsonSerialize(): array
    {
        $data = ['name' => $this->name];
        if (null !== $this->title) {
            $data['title'] = $this->title;
        }
        if (null !== $this->description) {
            $data['description'] = $this->description;
        }
        if (null !== $this->arguments) {
            $data['arguments'] = $this->arguments;
        }
        if (null !== $this->icons) {
            $data['icons'] = $this->icons;
        }
        if (null !== $this->meta) {
            $data['_meta'] = $this->meta;
        }

        return $data;
    }
}
