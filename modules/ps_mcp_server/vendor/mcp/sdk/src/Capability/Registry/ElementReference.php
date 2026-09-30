<?php



namespace Mcp\Capability\Registry;


class ElementReference
{
    
    public function __construct(
        public readonly \Closure|array|string $handler,
        public readonly bool $isManual = false,
    ) {
    }
}
