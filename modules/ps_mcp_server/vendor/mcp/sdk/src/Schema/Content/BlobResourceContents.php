<?php



namespace Mcp\Schema\Content;

use Mcp\Exception\InvalidArgumentException;


class BlobResourceContents extends ResourceContents
{
    
    public function __construct(
        string $uri,
        ?string $mimeType,
        public readonly string $blob,
        ?array $meta = null,
    ) {
        parent::__construct($uri, $mimeType, $meta);
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['uri']) || !\is_string($data['uri'])) {
            throw new InvalidArgumentException('Missing or invalid "uri" for BlobResourceContents.');
        }
        if (!isset($data['blob']) || !\is_string($data['blob'])) {
            throw new InvalidArgumentException('Missing or invalid "blob" for BlobResourceContents.');
        }

        return new self($data['uri'], $data['mimeType'] ?? null, $data['blob'], $data['_meta'] ?? null);
    }

    
    public static function fromStream(string $uri, $stream, string $mimeType, ?array $meta = null): self
    {
        $blob = stream_get_contents($stream);

        return new self($uri, $mimeType, base64_encode($blob), $meta);
    }

    
    public static function fromSplFileInfo(string $uri, \SplFileInfo $file, ?string $explicitMimeType = null, ?array $meta = null): self
    {
        $mimeType = $explicitMimeType ?? mime_content_type($file->getPathname());
        $blob = file_get_contents($file->getPathname());

        return new self($uri, $mimeType, base64_encode($blob), $meta);
    }

    
    public function jsonSerialize(): array
    {
        return [
            'blob' => $this->blob,
            ...parent::jsonSerialize(),
        ];
    }
}
