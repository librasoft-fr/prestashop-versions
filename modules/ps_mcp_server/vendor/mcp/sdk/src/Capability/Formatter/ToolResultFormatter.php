<?php



namespace Mcp\Capability\Formatter;

use Mcp\Schema\Content\Content;
use Mcp\Schema\Content\TextContent;


final class ToolResultFormatter
{
    
    public function format(mixed $toolExecutionResult): array
    {
        if ($toolExecutionResult instanceof Content) {
            return [$toolExecutionResult];
        }

        if (\is_array($toolExecutionResult)) {
            if (empty($toolExecutionResult)) {
                return [new TextContent('[]')];
            }

            $allAreContent = true;
            $hasContent = false;

            foreach ($toolExecutionResult as $item) {
                if ($item instanceof Content) {
                    $hasContent = true;
                } else {
                    $allAreContent = false;
                }
            }

            if ($allAreContent && $hasContent) {
                return $toolExecutionResult;
            }

            if ($hasContent) {
                $result = [];
                foreach ($toolExecutionResult as $item) {
                    if ($item instanceof Content) {
                        $result[] = $item;
                    } else {
                        $result = array_merge($result, $this->format($item));
                    }
                }

                return $result;
            }
        }

        if (null === $toolExecutionResult) {
            return [new TextContent('(null)')];
        }

        if (\is_bool($toolExecutionResult)) {
            return [new TextContent($toolExecutionResult ? 'true' : 'false')];
        }

        if (\is_scalar($toolExecutionResult)) {
            return [new TextContent($toolExecutionResult)];
        }

        $jsonResult = json_encode(
            $toolExecutionResult,
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR | \JSON_INVALID_UTF8_SUBSTITUTE
        );

        return [new TextContent($jsonResult)];
    }
}
