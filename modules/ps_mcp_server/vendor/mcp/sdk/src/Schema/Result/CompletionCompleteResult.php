<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\ResultInterface;


class CompletionCompleteResult implements ResultInterface
{
    
    public function __construct(
        public readonly array $values,
        public readonly ?int $total = null,
        public readonly ?bool $hasMore = null,
    ) {
        if (\count($this->values) > 100) {
            throw new InvalidArgumentException('Values must not exceed 100 items');
        }
    }

    
    public function jsonSerialize(): array
    {
        $completion = [
            'values' => $this->values,
        ];

        if (null !== $this->total) {
            $completion['total'] = $this->total;
        }
        if (null !== $this->hasMore) {
            $completion['hasMore'] = $this->hasMore;
        }

        return ['completion' => $completion];
    }

    
    public static function fromArray(array $data): self
    {
        $completion = $data['completion'] ?? [];

        return new self(
            $completion['values'] ?? [],
            $completion['total'] ?? null,
            $completion['hasMore'] ?? null,
        );
    }
}
