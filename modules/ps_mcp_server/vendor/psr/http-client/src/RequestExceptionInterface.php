<?php

namespace Psr\Http\Client;

use Psr\Http\Message\RequestInterface;


interface RequestExceptionInterface extends ClientExceptionInterface
{
    
    public function getRequest(): RequestInterface;
}
