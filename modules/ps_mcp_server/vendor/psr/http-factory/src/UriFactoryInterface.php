<?php

namespace Psr\Http\Message;

interface UriFactoryInterface
{
    
    public function createUri(string $uri = ''): UriInterface;
}
