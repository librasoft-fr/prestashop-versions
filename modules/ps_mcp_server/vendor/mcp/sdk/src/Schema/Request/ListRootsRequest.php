<?php



namespace Mcp\Schema\Request;

use Mcp\Schema\JsonRpc\Request;


final class ListRootsRequest extends Request
{
    public function __construct(
    ) {
    }

    public static function getMethod(): string
    {
        return 'roots/list';
    }

    protected static function fromParams(?array $params): static
    {
        return new self();
    }

    protected function getParams(): ?array
    {
        return null;
    }
}
