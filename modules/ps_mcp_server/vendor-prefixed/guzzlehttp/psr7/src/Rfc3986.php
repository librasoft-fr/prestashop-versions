<?php

declare(strict_types=1);

namespace PsMcpServerDeps\GuzzleHttp\Psr7;

final class Rfc3986
{
    public const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    public const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';
}
