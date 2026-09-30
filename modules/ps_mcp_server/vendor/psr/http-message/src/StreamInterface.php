<?php

declare(strict_types=1);

namespace Psr\Http\Message;


interface StreamInterface
{
    
    public function __toString();

    
    public function close();

    
    public function detach();

    
    public function getSize();

    
    public function tell();

    
    public function eof();

    
    public function isSeekable();

    
    public function seek(int $offset, int $whence = SEEK_SET);

    
    public function rewind();

    
    public function isWritable();

    
    public function write(string $string);

    
    public function isReadable();

    
    public function read(int $length);

    
    public function getContents();

    
    public function getMetadata(?string $key = null);
}
