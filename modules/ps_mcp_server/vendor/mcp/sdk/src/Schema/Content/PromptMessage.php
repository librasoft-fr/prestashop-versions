<?php



namespace Mcp\Schema\Content;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Enum\Role;


class PromptMessage extends Content
{
    
    public function __construct(
        public readonly Role $role,
        public readonly TextContent|ImageContent|AudioContent|EmbeddedResource $content,
    ) {
        parent::__construct('prompt');
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['role']) || !\is_string($data['role'])) {
            throw new InvalidArgumentException('Missing or invalid "role" in PromptMessage data.');
        }
        if (!isset($data['content']) || !\is_array($data['content'])) {
            throw new InvalidArgumentException('Missing or invalid "content" in PromptMessage data.');
        }

        $contentData = $data['content'];
        $contentType = $contentData['type'] ?? null;

        $content = match ($contentType) {
            'text' => TextContent::fromArray($contentData),
            'image' => ImageContent::fromArray($contentData),
            'audio' => AudioContent::fromArray($contentData),
            'resource' => EmbeddedResource::fromArray($contentData),
            default => throw new InvalidArgumentException(\sprintf('Invalid content type "%s" for PromptMessage.', $contentType)),
        };

        return new self(Role::from($data['role']), $content);
    }

    
    public function jsonSerialize(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }
}
