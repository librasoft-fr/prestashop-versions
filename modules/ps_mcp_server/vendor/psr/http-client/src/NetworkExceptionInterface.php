<?php

namespace Psr\Http\Client;

use Psr\Http\Message\RequestInterface;


interface NetworkExceptionInterface extends ClientExceptionInterface
{
    
    public function getRequest(): RequestInterface;
}
