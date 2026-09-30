<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

class PHPConfiguration
{
    public static function isBooleanIniOptionEnabled(string $option): bool
    {
        $value = \ini_get($option);

        if (empty($value)) {
            return false;
        }

        return \in_array(strtolower($value), ['1', 'on', 'true'], true);
    }
}
