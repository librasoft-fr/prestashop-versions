<?php

namespace PsMcpServerDeps\Mcp\Capability\Registry;

use PsMcpServerDeps\Mcp\Capability\Formatter\ResourceResultFormatter;
use PsMcpServerDeps\Mcp\Schema\ResourceTemplate;

class ResourceTemplateReference extends ElementReference
{
    private array $variableNames;

    private string $uriTemplateRegex;

    public function __construct(
        public readonly ResourceTemplate $resourceTemplate,
        callable|array|string $handler,
        bool $isManual = false,
        public readonly array $completionProviders = [],
    ) {
        parent::__construct($handler, $isManual);

        $this->compileTemplate();
    }

    public function getVariableNames(): array
    {
        return $this->variableNames;
    }

    public function matches(string $uri): bool
    {
        return 1 === preg_match($this->uriTemplateRegex, $uri);
    }

    public function extractVariables(string $uri): array
    {
        $matches = [];

        preg_match($this->uriTemplateRegex, $uri, $matches);

        return array_filter($matches, fn ($key) => \in_array($key, $this->variableNames), \ARRAY_FILTER_USE_KEY);
    }

    public function formatResult(mixed $readResult, string $uri, ?string $mimeType = null): array
    {
        return (new ResourceResultFormatter())->format($readResult, $uri, $mimeType, $this->resourceTemplate->meta);
    }

    private function compileTemplate(): void
    {
        $this->variableNames = [];
        $regexParts = [];

        $segments = preg_split('/(\{\w+\})/', $this->resourceTemplate->uriTemplate, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);

        foreach ($segments as $segment) {
            if (preg_match('/^\{(\w+)\}$/', $segment, $matches)) {
                $varName = $matches[1];
                $this->variableNames[] = $varName;
                $regexParts[] = '(?P<' . $varName . '>[^/]+)';
            } else {
                $regexParts[] = preg_quote($segment, '#');
            }
        }

        $this->uriTemplateRegex = '#^' . implode('', $regexParts) . '$#';
    }
}
