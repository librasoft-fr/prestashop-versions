<?php

declare(strict_types=1);

namespace Psr\Http\Message;


interface MessageInterface
{
    
    public function getProtocolVersion();

    
    public function withProtocolVersion(string $version);

    
    public function getHeaders();

    
    public function hasHeader(string $name);

    
    public function getHeader(string $name);

    
    public function getHeaderLine(string $name);

    
    public function withHeader(string $name, $value);

    
    public function withAddedHeader(string $name, $value);

    
    public function withoutHeader(string $name);

    
    public function getBody();

    
    public function withBody(StreamInterface $body);
}
