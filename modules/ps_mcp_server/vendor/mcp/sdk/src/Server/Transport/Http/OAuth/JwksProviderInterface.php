<?php



namespace Mcp\Server\Transport\Http\OAuth;


interface JwksProviderInterface
{
    
    public function getJwks(string $issuer, ?string $jwksUri = null): array;
}
