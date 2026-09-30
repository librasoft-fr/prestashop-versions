<?php



namespace Mcp\Schema;

use Mcp\Exception\InvalidArgumentException;


class Resource implements \JsonSerializable
{
    
    private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';

    
    private const URI_PATTERN = '/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\/[^\s]*$/';

    
    public function __construct(
        public readonly string $uri,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $mimeType = null,
        public readonly ?Annotations $annotations = null,
        public readonly ?int $size = null,
        public readonly ?array $icons = null,
        public readonly ?array $meta = null,
    ) {
        if (!preg_match(self::RESOURCE_NAME_PATTERN, $name)) {
            throw new InvalidArgumentException(\sprintf('Invalid resource name "%s": must contain only alphanumeric characters, underscores, and hyphens.', $name));
        }
        if (!preg_match(self::URI_PATTERN, $uri)) {
            throw new InvalidArgumentException(\sprintf('Invalid resource URI: "%s" must be a valid URI with a scheme and optional path.', $uri));
        }
    }

    
    public static function fromArray(array $data): self
    {
        if (empty($data['uri']) || !\is_string($data['uri'])) {
            throw new InvalidArgumentException('Invalid or missing "uri" in Resource data.');
        }
        if (empty($data['name']) || !\is_string($data['name'])) {
            throw new InvalidArgumentException('Invalid or missing "name" in Resource data.');
        }

        if (!empty($data['_meta']) && !\is_array($data['_meta'])) {
            throw new InvalidArgumentException('Invalid "_meta" in Resource data.');
        }

        return new self(
            uri: $data['uri'],
            name: $data['name'],
            description: $data['description'] ?? null,
            mimeType: $data['mimeType'] ?? null,
            annotations: isset($data['annotations']) ? Annotations::fromArray($data['annotations']) : null,
            size: isset($data['size']) ? (int) $data['size'] : null,
            icons: isset($data['icons']) && \is_array($data['icons']) ? array_map(Icon::fromArray(...), $data['icons']) : null,
            meta: isset($data['_meta']) ? $data['_meta'] : null
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = [
            'uri' => $this->uri,
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
        if (null !== $this->size) {
            $data['size'] = $this->size;
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
