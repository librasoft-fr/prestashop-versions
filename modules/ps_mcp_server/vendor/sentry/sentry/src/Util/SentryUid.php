<?php

declare(strict_types=1);

namespace Sentry\Util;


final class SentryUid
{
    
    public static function generate(): string
    {
        if (\function_exists('uuid_create')) {
            return strtolower(str_replace('-', '', uuid_create(\UUID_TYPE_RANDOM)));
        }

        $uuid = bin2hex(random_bytes(16));

        return \sprintf('%08s%04s4%03s%04x%012s',
            
            substr($uuid, 0, 8),
            
            substr($uuid, 8, 4),
            
            
            substr($uuid, 13, 3),
            
            
            
            
            hexdec(substr($uuid, 16, 4)) & 0x3FFF | 0x8000,
            
            substr($uuid, 20, 12)
        );
    }
}
