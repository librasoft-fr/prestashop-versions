<?php
declare(strict_types=1);

namespace League\MimeTypeDetection;

interface ExtensionLookup
{
    public function lookupExtension(string $mimetype): ?string;

    
    public function lookupAllExtensions(string $mimetype): array;
}
