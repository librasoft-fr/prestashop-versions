<?php



namespace Mcp\Server\Transport\Http\OAuth;


interface OidcDiscoveryMetadataPolicyInterface
{
    public function isValid(mixed $metadata): bool;
}
