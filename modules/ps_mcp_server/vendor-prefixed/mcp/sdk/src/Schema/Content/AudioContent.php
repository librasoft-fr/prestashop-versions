<?php

namespace PsMcpServerDeps\Mcp\Schema\Content;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Exception\RuntimeException;
use PsMcpServerDeps\Mcp\Schema\Annotations;

class AudioContent extends Content
{
    public function __construct(
        public readonly string $data,
        public readonly string $mimeType,
        public readonly ?Annotations $annotations = null,
    ) {
        parent::__construct('audio');
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['data']) || !isset($data['mimeType'])) {
            throw new InvalidArgumentException('Invalid or missing "data" or "mimeType" in AudioContent data.');
        }

        return new self(
            $data['data'],
            $data['mimeType'],
            isset($data['annotations']) ? Annotations::fromArray($data['annotations']) : null
        );
    }

    public static function fromFile(string $path, ?string $mimeType = null, ?Annotations $annotations = null): self
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(\sprintf('Audio file not found: "%s".', $path));
        }

        $content = file_get_contents($path);
        if (false === $content) {
            throw new RuntimeException(\sprintf('Could not read audio file: "%s".', $path));
        }
        $data = base64_encode($content);
        $detectedMime = $mimeType ?? mime_content_type($path) ?: 'application/octet-stream';

        return new self($data, $detectedMime, $annotations);
    }

    public static function fromString(string $data, string $mimeType, ?Annotations $annotations = null): self
    {
        return new self(base64_encode($data), $mimeType, $annotations);
    }

    public function jsonSerialize(): array
    {
        $result = [
            'type' => 'audio',
            'data' => $this->data,
            'mimeType' => $this->mimeType,
        ];

        if (null !== $this->annotations) {
            $result['annotations'] = $this->annotations;
        }

        return $result;
    }
}
