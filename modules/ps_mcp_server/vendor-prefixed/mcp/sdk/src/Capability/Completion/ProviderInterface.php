<?php

namespace PsMcpServerDeps\Mcp\Capability\Completion;

interface ProviderInterface
{
    public function getCompletions(string $currentValue): array;
}
