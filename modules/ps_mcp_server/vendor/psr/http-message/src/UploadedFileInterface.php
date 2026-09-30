<?php

declare(strict_types=1);

namespace Psr\Http\Message;


interface UploadedFileInterface
{
    
    public function getStream();

    
    public function moveTo(string $targetPath);
    
    
    public function getSize();
    
    
    public function getError();
    
    
    public function getClientFilename();
    
    
    public function getClientMediaType();
}
