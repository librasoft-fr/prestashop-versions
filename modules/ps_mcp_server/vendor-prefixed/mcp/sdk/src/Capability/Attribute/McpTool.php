<?php

namespace PsMcpServerDeps\Mcp\Capability\Attribute;

use PsMcpServerDeps\Mcp\Schema\ToolAnnotations;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class McpTool
{
    public function __construct(
        public ?string $name = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?ToolAnnotations $annotations = null,
        public ?array $icons = null,
        public ?array $meta = null,
        public ?array $outputSchema = null,
    ) {
    }
}
