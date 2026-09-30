<?php

namespace PsMcpServerDeps\Mcp\Schema;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

class Icon implements \JsonSerializable
{
    public function __construct(
        public readonly string $src,
        public readonly ?string $mimeType = null,
        public readonly ?array $sizes = null,
    ) {
        if (empty($src)) {
            throw new InvalidArgumentException('Icon "src" must be a non-empty string.');
        }
        if (!preg_match('#^(https?://|data:)#', $src)) {
            throw new InvalidArgumentException('Icon "src" must be a valid URL or data URI.');
        }

        if (null !== $sizes) {
            foreach ($sizes as $size) {
                if (!\is_string($size)) {
                    throw new InvalidArgumentException('Each size in "sizes" must be a string.');
                }
                if (!preg_match('/^(any|\d+x\d+)$/', $size)) {
                    throw new InvalidArgumentException(\sprintf('Invalid size format "%s" in "sizes". Expected "WxH" or "any".', $size));
                }
            }
        }
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['src']) || !\is_string($data['src'])) {
            throw new InvalidArgumentException('Invalid or missing "src" in Icon data.');
        }

        return new self($data['src'], $data['mimeTypes'] ?? null, $data['sizes'] ?? null);
    }

    public function jsonSerialize(): array
    {
        $data = [
            'src' => $this->src,
        ];

        if (null !== $this->mimeType) {
            $data['mimeType'] = $this->mimeType;
        }

        if (null !== $this->sizes) {
            $data['sizes'] = $this->sizes;
        }

        return $data;
    }
}
