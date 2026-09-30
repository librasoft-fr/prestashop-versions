<?php

namespace PsMcpServerDeps\Mcp\Schema\Content;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class TextResourceContents extends ResourceContents
{
    public function __construct(
        string $uri,
        ?string $mimeType,
        public readonly string $text,
        ?array $meta = null,
    ) {
        parent::__construct($uri, $mimeType, $meta);
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['uri']) || !\is_string($data['uri'])) {
            throw new InvalidArgumentException('Missing or invalid "uri" for TextResourceContents.');
        }
        if (!isset($data['text']) || !\is_string($data['text'])) {
            throw new InvalidArgumentException('Missing or invalid "text" for TextResourceContents.');
        }

        return new self($data['uri'], $data['mimeType'] ?? null, $data['text'], $data['_meta'] ?? null);
    }

    public function jsonSerialize(): array
    {
        return [
            'text' => $this->text,
            ...parent::jsonSerialize(),
        ];
    }
}
