<?php

namespace PsMcpServerDeps\Mcp\Capability\Attribute;

use PsMcpServerDeps\Mcp\Capability\Completion\ProviderInterface;
use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class CompletionProvider
{
    public function __construct(
        public ?string $providerClass = null,
        public string|ProviderInterface|null $provider = null,
        public ?array $values = null,
        public ?string $enum = null,
    ) {
        if (1 !== \count(array_filter([$provider, $values, $enum]))) {
            throw new InvalidArgumentException('Only one of provider, values, or enum can be set.');
        }
    }
}
