<?php



namespace Mcp\Schema\Request;

use Mcp\Schema\JsonRpc\Request;


final class ListResourcesRequest extends Request
{
    
    public function __construct(
        public readonly ?string $cursor = null,
    ) {
    }

    public static function getMethod(): string
    {
        return 'resources/list';
    }

    protected static function fromParams(?array $params): static
    {
        return new self($params['cursor'] ?? null);
    }

    
    protected function getParams(): ?array
    {
        $params = [];
        if (null !== $this->cursor) {
            $params['cursor'] = $this->cursor;
        }

        return $params ?: null;
    }
}
