<?php



namespace Mcp\Capability\Completion;


interface ProviderInterface
{
    
    public function getCompletions(string $currentValue): array;
}
