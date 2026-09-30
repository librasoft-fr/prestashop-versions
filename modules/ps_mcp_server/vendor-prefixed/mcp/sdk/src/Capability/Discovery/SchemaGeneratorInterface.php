<?php

namespace PsMcpServerDeps\Mcp\Capability\Discovery;

interface SchemaGeneratorInterface
{
    public function generate(\Reflector $reflection): array;

    public function generateOutputSchema(\Reflector $reflection): ?array;
}
