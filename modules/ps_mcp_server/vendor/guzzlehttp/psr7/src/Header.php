<?php

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

final class Header
{
    
    public static function parse($header): array
    {
        static $trimmed = "\"'  \n\t\r";
        $params = $matches = [];

        foreach ((array) $header as $value) {
            foreach (self::splitList($value) as $val) {
                $part = [];
                foreach (self::splitParameters($val) as $kvp) {
                    if (preg_match_all('/<[^>]+>|[^=]+/', $kvp, $matches)) {
                        $m = $matches[0];
                        if (isset($m[1])) {
                            $part[trim($m[0], $trimmed)] = trim($m[1], $trimmed);
                        } else {
                            $part[] = trim($m[0], $trimmed);
                        }
                    }
                }
                if ($part) {
                    $params[] = $part;
                }
            }
        }

        return $params;
    }

    
    private static function splitParameters(string $value): array
    {
        $values = [];
        $start = 0;
        $isQuoted = false;
        $isEscaped = false;

        for ($i = 0, $max = \strlen($value); $i < $max; ++$i) {
            $char = $value[$i];

            if ($isEscaped) {
                $isEscaped = false;

                continue;
            }

            if ($isQuoted && $char === '\\') {
                $isEscaped = true;

                continue;
            }

            if ($char === '"') {
                $isQuoted = !$isQuoted;

                continue;
            }

            if (!$isQuoted && $char === ';') {
                $values[] = \substr($value, $start, $i - $start);
                $start = $i + 1;
            }
        }

        $values[] = \substr($value, $start);

        return $values;
    }

    
    public static function normalize($header): array
    {
        $result = [];
        foreach ((array) $header as $value) {
            foreach (self::splitList($value) as $parsed) {
                $result[] = $parsed;
            }
        }

        return $result;
    }

    
    public static function splitList($values): array
    {
        if (!\is_array($values)) {
            $values = [$values];
        }

        $result = [];
        foreach ($values as $value) {
            if (!\is_string($value)) {
                throw new \TypeError('$header must either be a string or an array containing strings.');
            }

            $v = '';
            $isQuoted = false;
            $isEscaped = false;
            for ($i = 0, $max = \strlen($value); $i < $max; ++$i) {
                if ($isEscaped) {
                    $v .= $value[$i];
                    $isEscaped = false;

                    continue;
                }

                if (!$isQuoted && $value[$i] === ',') {
                    $v = \trim($v);
                    if ($v !== '') {
                        $result[] = $v;
                    }

                    $v = '';
                    continue;
                }

                if ($isQuoted && $value[$i] === '\\') {
                    $isEscaped = true;
                    $v .= $value[$i];

                    continue;
                }
                if ($value[$i] === '"') {
                    $isQuoted = !$isQuoted;
                    $v .= $value[$i];

                    continue;
                }

                $v .= $value[$i];
            }

            $v = \trim($v);
            if ($v !== '') {
                $result[] = $v;
            }
        }

        return $result;
    }
}
