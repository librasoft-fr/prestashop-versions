<?php

namespace PsMcpServerDeps\Mcp\Schema\Result;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\Content\BlobResourceContents;
use PsMcpServerDeps\Mcp\Schema\Content\TextResourceContents;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\ResultInterface;

class ReadResourceResult implements ResultInterface
{
    public function __construct(
        public readonly array $contents,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['contents']) || !\is_array($data['contents'])) {
            throw new InvalidArgumentException('Missing or invalid "contents" array in ReadResourceResult data.');
        }

        $contents = [];
        foreach ($data['contents'] as $content) {
            if (isset($content['text'])) {
                $contents[] = TextResourceContents::fromArray($content);
            } elseif (isset($content['blob'])) {
                $contents[] = BlobResourceContents::fromArray($content);
            } else {
                throw new InvalidArgumentException('Invalid content type in ReadResourceResult data: ' . json_encode($content));
            }
        }

        return new self($contents);
    }

    public function jsonSerialize(): array
    {
        return [
            'contents' => $this->contents,
        ];
    }
}
