<?php



namespace Mcp\Schema;


class ResourceReference implements \JsonSerializable
{
    public string $type = 'ref/resource';

    
    public function __construct(
        public readonly string $uri,
    ) {
    }

    
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'uri' => $this->uri,
        ];
    }
}
