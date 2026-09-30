<?php



namespace Mcp\Schema;


class PromptReference implements \JsonSerializable
{
    public string $type = 'ref/prompt';

    
    public function __construct(
        public readonly string $name,
    ) {
    }

    
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
        ];
    }
}
