<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Http\OAuth;

interface AuthorizationTokenValidatorInterface
{
    public function validate(string $accessToken): AuthorizationResult;
}
