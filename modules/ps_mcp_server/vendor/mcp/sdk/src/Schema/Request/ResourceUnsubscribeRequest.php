<?php



namespace Mcp\Schema\Request;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Request;


final class ResourceUnsubscribeRequest extends Request
{
    
    public function __construct(
        public readonly string $uri,
    ) {
    }

    public static function getMethod(): string
    {
        return 'resources/unsubscribe';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['uri']) || !\is_string($params['uri']) || empty($params['uri'])) {
            throw new InvalidArgumentException('Missing or invalid "uri" parameter for resources/unsubscribe.');
        }

        return new self($params['uri']);
    }

    
    protected function getParams(): array
    {
        return ['uri' => $this->uri];
    }
}
