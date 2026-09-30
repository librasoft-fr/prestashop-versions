<?php



namespace Mcp\Capability\Attribute;

use Mcp\Schema\Annotations;
use Mcp\Schema\Icon;


#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class McpResource
{
    
    public function __construct(
        public string $uri,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $mimeType = null,
        public ?int $size = null,
        public ?Annotations $annotations = null,
        public ?array $icons = null,
        public ?array $meta = null,
    ) {
    }
}
