<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

use PsMcpServerDeps\Sentry\Options;

trait PrefixStripper
{
    protected function stripPrefixFromFilePath(?Options $options, string $filePath): string
    {
        if ($options === null) {
            return $filePath;
        }

        foreach ($options->getPrefixes() as $prefix) {
            if (mb_substr($filePath, 0, mb_strlen($prefix)) === $prefix) {
                return mb_substr($filePath, mb_strlen($prefix));
            }
        }

        return $filePath;
    }
}
