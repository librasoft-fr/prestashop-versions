<?php



namespace Mcp\Capability\Registry;

use Mcp\Capability\Formatter\PromptResultFormatter;
use Mcp\Schema\Content\PromptMessage;
use Mcp\Schema\Prompt;


class PromptReference extends ElementReference
{
    
    public function __construct(
        public readonly Prompt $prompt,
        \Closure|array|string $handler,
        bool $isManual = false,
        public readonly array $completionProviders = [],
    ) {
        parent::__construct($handler, $isManual);
    }

    
    public function formatResult(mixed $promptGenerationResult): array
    {
        return (new PromptResultFormatter())->format($promptGenerationResult);
    }
}
