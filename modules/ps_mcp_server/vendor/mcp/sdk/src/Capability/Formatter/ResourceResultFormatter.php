<?php



namespace Mcp\Capability\Formatter;

use Mcp\Exception\RuntimeException;
use Mcp\Schema\Content\BlobResourceContents;
use Mcp\Schema\Content\EmbeddedResource;
use Mcp\Schema\Content\ResourceContents;
use Mcp\Schema\Content\TextResourceContents;


final class ResourceResultFormatter
{
    
    public function format(mixed $readResult, string $uri, ?string $mimeType = null, mixed $meta = null): array
    {
        if ($readResult instanceof ResourceContents) {
            return [$readResult];
        }

        if ($readResult instanceof EmbeddedResource) {
            return [$readResult->resource];
        }

        if (\is_array($readResult)) {
            if (empty($readResult)) {
                return [new TextResourceContents($uri, 'application/json', '[]', $meta)];
            }

            $allAreResourceContents = true;
            $hasResourceContents = false;
            $allAreEmbeddedResource = true;
            $hasEmbeddedResource = false;

            foreach ($readResult as $item) {
                if ($item instanceof ResourceContents) {
                    $hasResourceContents = true;
                    $allAreEmbeddedResource = false;
                } elseif ($item instanceof EmbeddedResource) {
                    $hasEmbeddedResource = true;
                    $allAreResourceContents = false;
                } else {
                    $allAreResourceContents = false;
                    $allAreEmbeddedResource = false;
                }
            }

            if ($allAreResourceContents && $hasResourceContents) {
                return $readResult;
            }

            if ($allAreEmbeddedResource && $hasEmbeddedResource) {
                return array_map(static fn ($item) => $item->resource, $readResult);
            }

            if ($hasResourceContents || $hasEmbeddedResource) {
                $result = [];
                foreach ($readResult as $item) {
                    if ($item instanceof ResourceContents) {
                        $result[] = $item;
                    } elseif ($item instanceof EmbeddedResource) {
                        $result[] = $item->resource;
                    } else {
                        $result = array_merge($result, $this->format($item, $uri, $mimeType, $meta));
                    }
                }

                return $result;
            }
        }

        if (\is_string($readResult)) {
            $mimeType = $mimeType ?? $this->guessMimeTypeFromString($readResult);

            return [new TextResourceContents($uri, $mimeType, $readResult, $meta)];
        }

        if (\is_resource($readResult) && 'stream' === get_resource_type($readResult)) {
            $result = BlobResourceContents::fromStream(
                $uri,
                $readResult,
                $mimeType ?? 'application/octet-stream',
                $meta
            );

            @fclose($readResult);

            return [$result];
        }

        if (\is_array($readResult) && isset($readResult['blob']) && \is_string($readResult['blob'])) {
            $mimeType = $readResult['mimeType'] ?? $mimeType ?? 'application/octet-stream';

            return [new BlobResourceContents($uri, $mimeType, $readResult['blob'], $meta)];
        }

        if (\is_array($readResult) && isset($readResult['text']) && \is_string($readResult['text'])) {
            $mimeType = $readResult['mimeType'] ?? $mimeType ?? 'text/plain';

            return [new TextResourceContents($uri, $mimeType, $readResult['text'], $meta)];
        }

        if ($readResult instanceof \SplFileInfo && $readResult->isFile() && $readResult->isReadable()) {
            if ($mimeType && str_contains(strtolower($mimeType), 'text')) {
                return [new TextResourceContents($uri, $mimeType, file_get_contents($readResult->getPathname()), $meta)];
            }

            return [BlobResourceContents::fromSplFileInfo($uri, $readResult, $mimeType, $meta)];
        }

        if (\is_array($readResult)) {
            if ($mimeType && (str_contains(strtolower($mimeType), 'json')
                || 'application/json' === $mimeType)) {
                try {
                    $jsonString = json_encode($readResult, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);

                    return [new TextResourceContents($uri, $mimeType, $jsonString, $meta)];
                } catch (\JsonException $e) {
                    throw new RuntimeException(\sprintf('Failed to encode array as JSON for URI "%s": %s', $uri, $e->getMessage()));
                }
            }

            try {
                $jsonString = json_encode($readResult, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
                $mimeType = $mimeType ?? 'application/json';

                return [new TextResourceContents($uri, $mimeType, $jsonString, $meta)];
            } catch (\JsonException $e) {
                throw new RuntimeException(\sprintf('Failed to encode array as JSON for URI "%s": %s', $uri, $e->getMessage()));
            }
        }

        throw new RuntimeException(\sprintf('Cannot format resource read result for URI "%s". Handler method returned unhandled type: ', $uri).\gettype($readResult));
    }

    
    private function guessMimeTypeFromString(string $content): string
    {
        $trimmed = ltrim($content);

        if (str_starts_with($trimmed, '<') && str_ends_with(rtrim($content), '>')) {
            if (str_contains($trimmed, '<html')) {
                return 'text/html';
            }
            if (str_contains($trimmed, '<?xml')) {
                return 'application/xml';
            }

            return 'text/plain';
        }

        if (str_starts_with($trimmed, '{') && str_ends_with(rtrim($content), '}')) {
            return 'application/json';
        }

        if (str_starts_with($trimmed, '[') && str_ends_with(rtrim($content), ']')) {
            return 'application/json';
        }

        return 'text/plain';
    }
}
