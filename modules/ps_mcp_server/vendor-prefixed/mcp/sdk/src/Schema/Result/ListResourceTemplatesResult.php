<?php

namespace PsMcpServerDeps\Mcp\Schema\Result;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\ResultInterface;
use PsMcpServerDeps\Mcp\Schema\ResourceTemplate;

class ListResourceTemplatesResult implements ResultInterface
{
    public function __construct(
        public readonly array $resourceTemplates,
        public readonly ?string $nextCursor = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['resourceTemplates']) || !\is_array($data['resourceTemplates'])) {
            throw new InvalidArgumentException('Missing or invalid "resourceTemplates" array in ListResourceTemplatesResult data.');
        }

        return new self(
            array_map(static fn (array $resourceTemplate) => ResourceTemplate::fromArray($resourceTemplate), $data['resourceTemplates']),
            $data['nextCursor'] ?? null
        );
    }

    public function jsonSerialize(): array
    {
        $result = [
            'resourceTemplates' => array_values($this->resourceTemplates),
        ];

        if ($this->nextCursor) {
            $result['nextCursor'] = $this->nextCursor;
        }

        return $result;
    }
}
