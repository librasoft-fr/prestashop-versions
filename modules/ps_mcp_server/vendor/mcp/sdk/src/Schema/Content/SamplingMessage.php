<?php



namespace Mcp\Schema\Content;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Enum\Role;


class SamplingMessage extends Content
{
    public function __construct(
        public readonly Role $role,
        public readonly TextContent|ImageContent|AudioContent $content,
    ) {
        parent::__construct('sampling');
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['role']) || !\is_string($data['role'])) {
            throw new InvalidArgumentException('Missing or invalid "role" in SamplingMessage data.');
        }
        if (!isset($data['content']) || !\is_array($data['content'])) {
            throw new InvalidArgumentException('Missing or invalid "content" in SamplingMessage data.');
        }

        $role = Role::from($data['role']);
        $contentData = $data['content'];
        $contentType = $contentData['type'] ?? null;

        $contentInstance = match ($contentType) {
            'text' => TextContent::fromArray($contentData),
            'image' => ImageContent::fromArray($contentData),
            'audio' => AudioContent::fromArray($contentData),
            default => throw new InvalidArgumentException(\sprintf('Invalid content type "%s" for SamplingMessage.', $contentType)),
        };

        return new self($role, $contentInstance);
    }

    
    public function jsonSerialize(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }
}
