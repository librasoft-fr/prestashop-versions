<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Formats;

use PsMcpServerDeps\Opis\JsonSchema\Uri;
use PsMcpServerDeps\Opis\Uri\UriTemplate;

class UriFormats
{
    public static function uri(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $uri = Uri::parse($value);

        return $uri !== null && $uri->isAbsolute();
    }

    public static function uriReference(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        return Uri::parse($value) !== null;
    }

    public static function uriTemplate(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        if (UriTemplate::isTemplate($value)) {
            return true;
        }

        return Uri::parse($value) !== null;
    }
}
