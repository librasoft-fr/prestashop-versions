<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Content\AudioContent;
use Mcp\Schema\Content\Content;
use Mcp\Schema\Content\EmbeddedResource;
use Mcp\Schema\Content\ImageContent;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\JsonRpc\ResultInterface;


class CallToolResult implements ResultInterface
{
    
    public function __construct(
        public readonly array $content,
        public readonly bool $isError = false,
        public readonly ?array $structuredContent = null,
        public readonly ?array $meta = null,
    ) {
        foreach ($this->content as $item) {
            if (!$item instanceof Content) {
                throw new InvalidArgumentException('Content must be an array of Content objects.');
            }
        }
    }

    
    public static function success(array $content, ?array $meta = null): self
    {
        return new self($content, false, null, $meta);
    }

    
    public static function error(array $content, ?array $meta = null): self
    {
        return new self($content, true, null, $meta);
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['content']) || !\is_array($data['content'])) {
            throw new InvalidArgumentException('Missing or invalid "content" array in CallToolResult data.');
        }

        $contents = [];

        foreach ($data['content'] as $item) {
            $contents[] = match ($item['type'] ?? null) {
                'text' => TextContent::fromArray($item),
                'image' => ImageContent::fromArray($item),
                'audio' => AudioContent::fromArray($item),
                'resource' => EmbeddedResource::fromArray($item),
                default => throw new InvalidArgumentException(\sprintf('Invalid content type in CallToolResult data: "%s".', $item['type'] ?? null)),
            };
        }

        return new self(
            $contents,
            $data['isError'] ?? false,
            $data['structuredContent'] ?? null,
            $data['_meta'] ?? null
        );
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'content' => $this->content,
            'isError' => $this->isError,
        ];

        if ($this->structuredContent) {
            $result['structuredContent'] = $this->structuredContent;
        }

        if ($this->meta) {
            $result['_meta'] = $this->meta;
        }

        return $result;
    }
}
