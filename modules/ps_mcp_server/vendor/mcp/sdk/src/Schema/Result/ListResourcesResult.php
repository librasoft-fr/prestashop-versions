<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\JsonRpc\ResultInterface;
use Mcp\Schema\Resource as ResourceSchema;


class ListResourcesResult implements ResultInterface
{
    
    public function __construct(
        public readonly array $resources,
        public readonly ?string $nextCursor = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['resources']) || !\is_array($data['resources'])) {
            throw new InvalidArgumentException('Missing or invalid "resources" array in ListResourcesResult data.');
        }

        return new self(
            array_map(static fn (array $resource) => ResourceSchema::fromArray($resource), $data['resources']),
            $data['nextCursor'] ?? null
        );
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'resources' => array_values($this->resources),
        ];

        if (null !== $this->nextCursor) {
            $result['nextCursor'] = $this->nextCursor;
        }

        return $result;
    }
}
