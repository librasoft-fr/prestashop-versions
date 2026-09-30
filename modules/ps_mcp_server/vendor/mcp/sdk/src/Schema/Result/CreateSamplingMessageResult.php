<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Content\AudioContent;
use Mcp\Schema\Content\ImageContent;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Enum\Role;
use Mcp\Schema\JsonRpc\ResultInterface;


class CreateSamplingMessageResult implements ResultInterface
{
    
    public function __construct(
        public readonly Role $role,
        public readonly TextContent|ImageContent|AudioContent $content,
        public readonly string $model,
        public readonly ?string $stopReason = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['role']) || !\is_string($data['role'])) {
            throw new InvalidArgumentException('Missing or invalid "role" in CreateSamplingMessageResult data.');
        }

        if (!isset($data['content']) || !\is_array($data['content'])) {
            throw new InvalidArgumentException('Missing or invalid "content" in CreateSamplingMessageResult data.');
        }

        if (!isset($data['model']) || !\is_string($data['model'])) {
            throw new InvalidArgumentException('Missing or invalid "model" in CreateSamplingMessageResult data.');
        }

        $role = Role::from($data['role']);
        $contentPayload = $data['content'];

        $content = self::hydrateContent($contentPayload);
        $stopReason = isset($data['stopReason']) && \is_string($data['stopReason']) ? $data['stopReason'] : null;

        return new self($role, $content, $data['model'], $stopReason);
    }

    
    private static function hydrateContent(array $contentData): TextContent|ImageContent|AudioContent
    {
        $type = $contentData['type'] ?? null;

        if (!\is_string($type)) {
            throw new InvalidArgumentException('Missing or invalid "type" in sampling content payload.');
        }

        return match ($type) {
            'text' => TextContent::fromArray($contentData),
            'image' => ImageContent::fromArray($contentData),
            'audio' => AudioContent::fromArray($contentData),
            default => throw new InvalidArgumentException(\sprintf('Unsupported sampling content type "%s".', $type)),
        };
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'role' => $this->role->value,
            'content' => $this->content,
            'model' => $this->model,
        ];

        if (null !== $this->stopReason) {
            $result['stopReason'] = $this->stopReason;
        }

        return $result;
    }
}
