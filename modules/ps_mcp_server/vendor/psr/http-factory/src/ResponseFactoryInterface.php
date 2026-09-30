<?php

namespace Psr\Http\Message;

interface ResponseFactoryInterface
{
    
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface;
}
