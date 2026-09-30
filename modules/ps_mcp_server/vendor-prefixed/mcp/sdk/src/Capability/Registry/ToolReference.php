<?php

namespace PsMcpServerDeps\Mcp\Capability\Registry;

use PsMcpServerDeps\Mcp\Capability\Formatter\ToolResultFormatter;
use PsMcpServerDeps\Mcp\Schema\Content\Content;
use PsMcpServerDeps\Mcp\Schema\Tool;

class ToolReference extends ElementReference
{
    public function __construct(
        public readonly Tool $tool,
        callable|array|string $handler,
        bool $isManual = false,
    ) {
        parent::__construct($handler, $isManual);
    }

    public function formatResult(mixed $toolExecutionResult): array
    {
        return (new ToolResultFormatter())->format($toolExecutionResult);
    }

    public function extractStructuredContent(mixed $toolExecutionResult): ?array
    {
        if (\is_array($toolExecutionResult)) {
            return $toolExecutionResult;
        }

        if (\is_object($toolExecutionResult) && !($toolExecutionResult instanceof Content)) {
            $jsonResult = json_encode(
                $toolExecutionResult,
                \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR | \JSON_INVALID_UTF8_SUBSTITUTE
            );

            return json_decode(
                $jsonResult, true, 512, \JSON_THROW_ON_ERROR
            );
        }

        return null;
    }
}
