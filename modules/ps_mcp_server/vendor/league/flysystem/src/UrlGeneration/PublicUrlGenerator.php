<?php

declare(strict_types=1);

namespace League\Flysystem\UrlGeneration;

use League\Flysystem\Config;
use League\Flysystem\UnableToGeneratePublicUrl;

interface PublicUrlGenerator
{
    
    public function publicUrl(string $path, Config $config): string;
}
