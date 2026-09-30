<?php

namespace PsMcpServerDeps\Mcp\Schema;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class ResourceTemplate implements \JsonSerializable
{
    private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';

    private const URI_TEMPLATE_PATTERN = '/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\/.*{[^{}]+}.*/';

    public function __construct(
        public readonly string $uriTemplate,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $mimeType = null,
        public readonly ?Annotations $annotations = null,
        public readonly ?array $meta = null,
    ) {
        if (!preg_match(self::RESOURCE_NAME_PATTERN, $name)) {
            throw new InvalidArgumentException(\sprintf('Invalid resource name "%s": must contain only alphanumeric characters, underscores, and hyphens.', $name));
        }
        if (!preg_match(self::URI_TEMPLATE_PATTERN, $uriTemplate)) {
            throw new InvalidArgumentException(\sprintf('Invalid URI template : "%s" must be a valid URI template with at least one placeholder.', $uriTemplate));
        }
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['uriTemplate']) || !\is_string($data['uriTemplate'])) {
            throw new InvalidArgumentException('Invalid or missing "uriTemplate" in ResourceTemplate data.');
        }
        if (empty($data['name']) || !\is_string($data['name'])) {
            throw new InvalidArgumentException('Invalid or missing "name" in ResourceTemplate data.');
        }

        if (!empty($data['_meta']) && !\is_array($data['_meta'])) {
            throw new InvalidArgumentException('Invalid "_meta" in ResourceTemplate data.');
        }

        return new self(
            uriTemplate: $data['uriTemplate'],
            name: $data['name'],
            description: $data['description'] ?? null,
            mimeType: $data['mimeType'] ?? null,
            annotations: isset($data['annotations']) ? Annotations::fromArray($data['annotations']) : null,
            meta: isset($data['_meta']) ? $data['_meta'] : null
        );
    }

    public function jsonSerialize(): array
    {
        $data = [
            'uriTemplate' => $this->uriTemplate,
            'name' => $this->name,
        ];
        if (null !== $this->description) {
            $data['description'] = $this->description;
        }
        if (null !== $this->mimeType) {
            $data['mimeType'] = $this->mimeType;
        }
        if (null !== $this->annotations) {
            $data['annotations'] = $this->annotations;
        }
        if (null !== $this->meta) {
            $data['_meta'] = $this->meta;
        }

        return $data;
    }
}
