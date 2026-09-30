<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Http\OAuth;

interface OidcDiscoveryInterface
{
    public function discover(string $issuer): array;

    public function getAuthorizationEndpoint(string $issuer): string;

    public function getTokenEndpoint(string $issuer): string;

    public function getJwksUri(string $issuer): string;
}
