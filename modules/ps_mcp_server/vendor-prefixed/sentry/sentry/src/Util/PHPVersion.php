<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

final class PHPVersion
{
    private const VERSION_PARSING_REGEX = '/^(?<base>\d\.\d\.\d{1,2})(?<extra>-(beta|rc)-?(\d+)?(-dev)?)?/i';

    public static function parseVersion(string $version = \PHP_VERSION): string
    {
        $matches = [];
        if (!preg_match(self::VERSION_PARSING_REGEX, $version, $matches)) {
            return $version;
        }

        $version = $matches['base'];

        if (isset($matches['extra'])) {
            $version .= $matches['extra'];
        }

        return $version;
    }
}
