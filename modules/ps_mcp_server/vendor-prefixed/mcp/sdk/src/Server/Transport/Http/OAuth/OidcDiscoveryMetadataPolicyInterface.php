<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Http\OAuth;

interface OidcDiscoveryMetadataPolicyInterface
{
    public function isValid(mixed $metadata): bool;
}
