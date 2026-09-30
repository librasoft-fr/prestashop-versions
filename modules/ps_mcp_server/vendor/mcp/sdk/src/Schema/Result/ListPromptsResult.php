<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\JsonRpc\ResultInterface;
use Mcp\Schema\Prompt;


class ListPromptsResult implements ResultInterface
{
    
    public function __construct(
        public readonly array $prompts,
        public readonly ?string $nextCursor = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['prompts']) || !\is_array($data['prompts'])) {
            throw new InvalidArgumentException('Missing or invalid "prompts" array in ListPromptsResult data.');
        }

        return new self(
            array_map(static fn (array $prompt) => Prompt::fromArray($prompt), $data['prompts']),
            $data['nextCursor'] ?? null
        );
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'prompts' => array_values($this->prompts),
        ];

        if ($this->nextCursor) {
            $result['nextCursor'] = $this->nextCursor;
        }

        return $result;
    }
}
