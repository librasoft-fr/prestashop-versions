<?php



namespace Mcp\Schema\Content;

use Mcp\Exception\InvalidArgumentException;


class ImageContent extends Content
{
    
    public function __construct(
        public readonly string $data,
        public readonly string $mimeType,
    ) {
        parent::__construct('image');
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['data']) || !\is_string($data['data'])) {
            throw new InvalidArgumentException('Missing or invalid "data" in ImageContent data.');
        }
        if (!isset($data['mimeType']) || !\is_string($data['mimeType'])) {
            throw new InvalidArgumentException('Missing or invalid "mimeType" in ImageContent data.');
        }

        return new self($data['data'], $data['mimeType']);
    }

    
    public static function fromFile(string $path, ?string $mimeType = null): self
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(\sprintf('Image file not found: "%s".', $path));
        }

        $data = base64_encode(file_get_contents($path));
        $detectedMime = $mimeType ?? mime_content_type($path) ?: 'image/png';

        return new self($data, $detectedMime);
    }

    public static function fromString(string $data, string $mimeType): self
    {
        return new self(base64_encode($data), $mimeType);
    }

    
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'mimeType' => $this->mimeType,
        ];
    }
}
