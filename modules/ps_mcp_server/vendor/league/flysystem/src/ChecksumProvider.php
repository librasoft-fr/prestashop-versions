<?php

namespace League\Flysystem;

interface ChecksumProvider
{
    
    public function checksum(string $path, Config $config): string;
}
