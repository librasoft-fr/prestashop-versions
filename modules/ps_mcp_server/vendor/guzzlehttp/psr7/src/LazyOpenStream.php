<?php

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;


final class LazyOpenStream implements StreamInterface
{
    use StreamDecoratorTrait;

    
    private $filename;

    
    private $mode;

    
    private $stream;

    
    public function __construct(string $filename, string $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;

        
        
        unset($this->stream);
    }

    
    protected function createStream(): StreamInterface
    {
        return Utils::streamFor(Utils::tryFopen($this->filename, $this->mode));
    }
}
