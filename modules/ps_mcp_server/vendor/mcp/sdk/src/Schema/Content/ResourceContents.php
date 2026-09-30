<?php



namespace Mcp\Schema\Content;


abstract class ResourceContents implements \JsonSerializable
{
    
    public function __construct(
        public readonly string $uri,
        public readonly ?string $mimeType = null,
        public readonly ?array $meta = null,
    ) {
    }

    
    public function jsonSerialize(): array
    {
        $data = ['uri' => $this->uri];
        if (null !== $this->mimeType) {
            $data['mimeType'] = $this->mimeType;
        }

        if (null !== $this->meta) {
            $data['_meta'] = $this->meta;
        }

        return $data;
    }
}
