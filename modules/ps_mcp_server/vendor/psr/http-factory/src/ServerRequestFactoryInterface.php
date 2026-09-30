<?php

namespace Psr\Http\Message;

interface ServerRequestFactoryInterface
{
    
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface;
}
