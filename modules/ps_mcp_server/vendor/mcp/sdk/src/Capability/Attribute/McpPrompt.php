<?php



namespace Mcp\Capability\Attribute;

use Mcp\Schema\Icon;


#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class McpPrompt
{
    
    public function __construct(
        public ?string $name = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?array $icons = null,
        public ?array $meta = null,
    ) {
    }
}
