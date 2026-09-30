<?php



namespace Mcp\Capability\Attribute;

use Mcp\Schema\Annotations;


#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class McpResourceTemplate
{
    
    public function __construct(
        public string $uriTemplate,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $mimeType = null,
        public ?Annotations $annotations = null,
        public ?array $meta = null,
    ) {
    }
}
