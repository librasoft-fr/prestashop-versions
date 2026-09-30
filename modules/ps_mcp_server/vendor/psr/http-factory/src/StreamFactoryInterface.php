<?php

namespace Psr\Http\Message;

interface StreamFactoryInterface
{
    
    public function createStream(string $content = ''): StreamInterface;

    
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface;

    
    public function createStreamFromResource($resource): StreamInterface;
}
