<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\JsonRpc\ResultInterface;
use Mcp\Schema\Tool;


class ListToolsResult implements ResultInterface
{
    
    public function __construct(
        public readonly array $tools,
        public readonly ?string $nextCursor = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['tools']) || !\is_array($data['tools'])) {
            throw new InvalidArgumentException('Missing or invalid "tools" array in ListToolsResult data.');
        }

        return new self(
            array_map(static fn (array $tool) => Tool::fromArray($tool), $data['tools']),
            $data['nextCursor'] ?? null
        );
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'tools' => array_values($this->tools),
        ];

        if ($this->nextCursor) {
            $result['nextCursor'] = $this->nextCursor;
        }

        return $result;
    }
}
