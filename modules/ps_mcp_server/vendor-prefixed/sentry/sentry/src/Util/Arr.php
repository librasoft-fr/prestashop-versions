<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

class Arr
{
    public static function simpleDot(array $array): array
    {
        $results = [];

        $flatten = static function ($data, $prefix = '') use (&$results, &$flatten): void {
            foreach ($data as $key => $value) {
                $newKey = $prefix . $key;

                if (\is_array($value) && !empty($value) && !self::isList($value)) {
                    $flatten($value, $newKey . '.');
                } else {
                    $results[$newKey] = $value;
                }
            }
        };

        $flatten($array);

        return $results;
    }

    public static function isList(array $array): bool
    {
        $i = 0;

        foreach ($array as $k => $v) {
            if ($k !== $i++) {
                return false;
            }
        }

        return true;
    }
}
