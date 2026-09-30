<?php



namespace Mcp\Schema\Result;

use Mcp\Schema\JsonRpc\ResultInterface;
use Mcp\Schema\Root;


class ListRootsResult implements ResultInterface
{
    
    public function __construct(
        public readonly array $roots,
        public readonly ?array $meta = null,
    ) {
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'roots' => array_values($this->roots),
        ];

        if (null !== $this->meta) {
            $result['_meta'] = $this->meta;
        }

        return $result;
    }
}
