<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Http\OAuth;

interface ClientRegistrarInterface
{
    public function register(array $registrationRequest): array;
}
