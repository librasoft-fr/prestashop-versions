<?php



namespace Mcp\Capability\Registry;

use Mcp\Capability\Formatter\ResourceResultFormatter;
use Mcp\Schema\Content\ResourceContents;
use Mcp\Schema\Resource;


class ResourceReference extends ElementReference
{
    
    public function __construct(
        public readonly Resource $resource,
        callable|array|string $handler,
        bool $isManual = false,
    ) {
        parent::__construct($handler, $isManual);
    }

    
    public function formatResult(mixed $readResult, string $uri, ?string $mimeType = null): array
    {
        return (new ResourceResultFormatter())->format($readResult, $uri, $mimeType, $this->resource->meta);
    }
}
