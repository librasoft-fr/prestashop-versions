<?php



namespace Mcp\Capability;

use Mcp\Capability\Discovery\DiscoveryState;
use Mcp\Capability\Registry\ElementReference;
use Mcp\Capability\Registry\PromptReference;
use Mcp\Capability\Registry\ResourceReference;
use Mcp\Capability\Registry\ResourceTemplateReference;
use Mcp\Capability\Registry\ToolReference;
use Mcp\Exception\PromptNotFoundException;
use Mcp\Exception\ResourceNotFoundException;
use Mcp\Exception\ToolNotFoundException;
use Mcp\Schema\Page;
use Mcp\Schema\Prompt;
use Mcp\Schema\Resource;
use Mcp\Schema\ResourceTemplate;
use Mcp\Schema\Tool;


interface RegistryInterface
{
    
    public function registerTool(Tool $tool, callable|array|string $handler, bool $isManual = false): void;

    
    public function registerResource(Resource $resource, callable|array|string $handler, bool $isManual = false): void;

    
    public function registerResourceTemplate(
        ResourceTemplate $template,
        callable|array|string $handler,
        array $completionProviders = [],
        bool $isManual = false,
    ): void;

    
    public function registerPrompt(
        Prompt $prompt,
        callable|array|string $handler,
        array $completionProviders = [],
        bool $isManual = false,
    ): void;

    
    public function clear(): void;

    
    public function getDiscoveryState(): DiscoveryState;

    
    public function setDiscoveryState(DiscoveryState $state): void;

    
    public function hasTools(): bool;

    
    public function getTools(?int $limit = null, ?string $cursor = null): Page;

    
    public function getTool(string $name): ToolReference;

    
    public function hasResources(): bool;

    
    public function getResources(?int $limit = null, ?string $cursor = null): Page;

    
    public function getResource(string $uri, bool $includeTemplates = true): ResourceReference|ResourceTemplateReference;

    
    public function hasResourceTemplates(): bool;

    
    public function getResourceTemplates(?int $limit = null, ?string $cursor = null): Page;

    
    public function getResourceTemplate(string $uriTemplate): ResourceTemplateReference;

    
    public function hasPrompts(): bool;

    
    public function getPrompts(?int $limit = null, ?string $cursor = null): Page;

    
    public function getPrompt(string $name): PromptReference;
}
