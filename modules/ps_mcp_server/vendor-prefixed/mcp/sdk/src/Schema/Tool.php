<?php

namespace PsMcpServerDeps\Mcp\Schema;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class Tool implements \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $title,
        public readonly array $inputSchema,
        public readonly ?string $description,
        public readonly ?ToolAnnotations $annotations,
        public readonly ?array $icons = null,
        public readonly ?array $meta = null,
        public readonly ?array $outputSchema = null,
    ) {
        if (!isset($inputSchema['type']) || 'object' !== $inputSchema['type']) {
            throw new InvalidArgumentException('Tool inputSchema must be a JSON Schema of type "object".');
        }
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['name']) || !\is_string($data['name'])) {
            throw new InvalidArgumentException('Invalid or missing "name" in Tool data.');
        }
        if (!isset($data['inputSchema']) || !\is_array($data['inputSchema'])) {
            throw new InvalidArgumentException('Invalid or missing "inputSchema" in Tool data.');
        }
        if (!isset($data['inputSchema']['type']) || 'object' !== $data['inputSchema']['type']) {
            throw new InvalidArgumentException('Tool inputSchema must be of type "object".');
        }
        $inputSchema = self::normalizeSchemaProperties($data['inputSchema']);

        $outputSchema = null;
        if (isset($data['outputSchema']) && \is_array($data['outputSchema'])) {
            if (!isset($data['outputSchema']['type']) || 'object' !== $data['outputSchema']['type']) {
                throw new InvalidArgumentException('Tool outputSchema must be of type "object".');
            }
            $outputSchema = self::normalizeSchemaProperties($data['outputSchema']);
        }

        return new self(
            name: $data['name'],
            title: isset($data['title']) && \is_string($data['title']) ? $data['title'] : null,
            inputSchema: $inputSchema,
            description: isset($data['description']) && \is_string($data['description']) ? $data['description'] : null,
            annotations: isset($data['annotations']) && \is_array($data['annotations']) ? ToolAnnotations::fromArray($data['annotations']) : null,
            icons: isset($data['icons']) && \is_array($data['icons']) ? array_map(Icon::fromArray(...), $data['icons']) : null,
            meta: isset($data['_meta']) && \is_array($data['_meta']) ? $data['_meta'] : null,
            outputSchema: $outputSchema,
        );
    }

    public function jsonSerialize(): array
    {
        $data = ['name' => $this->name];
        if (null !== $this->title) {
            $data['title'] = $this->title;
        }
        $data['inputSchema'] = $this->inputSchema;
        if (null !== $this->description) {
            $data['description'] = $this->description;
        }
        if (null !== $this->annotations) {
            $data['annotations'] = $this->annotations;
        }
        if (null !== $this->icons) {
            $data['icons'] = $this->icons;
        }
        if (null !== $this->meta) {
            $data['_meta'] = $this->meta;
        }
        if (null !== $this->outputSchema) {
            $data['outputSchema'] = $this->outputSchema;
        }

        return $data;
    }

    private static function normalizeSchemaProperties(array $schema): array
    {
        if (isset($schema['properties']) && \is_array($schema['properties']) && empty($schema['properties'])) {
            $schema['properties'] = new \stdClass();
        }

        return $schema;
    }
}
