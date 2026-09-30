<?php



namespace Mcp\Capability\Discovery;

use Mcp\Capability\Registry\PromptReference;
use Mcp\Capability\Registry\ResourceReference;
use Mcp\Capability\Registry\ResourceTemplateReference;
use Mcp\Capability\Registry\ToolReference;


final class DiscoveryState
{
    
    public function __construct(
        private readonly array $tools = [],
        private readonly array $resources = [],
        private readonly array $prompts = [],
        private readonly array $resourceTemplates = [],
    ) {
    }

    
    public function getTools(): array
    {
        return $this->tools;
    }

    
    public function getResources(): array
    {
        return $this->resources;
    }

    
    public function getPrompts(): array
    {
        return $this->prompts;
    }

    
    public function getResourceTemplates(): array
    {
        return $this->resourceTemplates;
    }

    
    public function isEmpty(): bool
    {
        return empty($this->tools)
            && empty($this->resources)
            && empty($this->prompts)
            && empty($this->resourceTemplates);
    }

    
    public function getElementCount(): int
    {
        return \count($this->tools)
            + \count($this->resources)
            + \count($this->prompts)
            + \count($this->resourceTemplates);
    }

    
    public function getElementCounts(): array
    {
        return [
            'tools' => \count($this->tools),
            'resources' => \count($this->resources),
            'prompts' => \count($this->prompts),
            'resourceTemplates' => \count($this->resourceTemplates),
        ];
    }
}
