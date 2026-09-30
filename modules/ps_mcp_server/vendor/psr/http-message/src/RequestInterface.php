<?php

declare(strict_types=1);

namespace Psr\Http\Message;


interface RequestInterface extends MessageInterface
{
    
    public function getRequestTarget();

    
    public function withRequestTarget(string $requestTarget);

    
    public function getMethod();

    
    public function withMethod(string $method);

    
    public function getUri();

    
    public function withUri(UriInterface $uri, bool $preserveHost = false);
}
