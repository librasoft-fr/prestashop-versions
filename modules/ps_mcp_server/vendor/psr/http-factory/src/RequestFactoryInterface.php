<?php

namespace Psr\Http\Message;

interface RequestFactoryInterface
{
    
    public function createRequest(string $method, $uri): RequestInterface;
}
