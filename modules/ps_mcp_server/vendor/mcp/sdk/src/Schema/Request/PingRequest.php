<?php



namespace Mcp\Schema\Request;

use Mcp\Schema\JsonRpc\Request;


final class PingRequest extends Request
{
    public static function getMethod(): string
    {
        return 'ping';
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
