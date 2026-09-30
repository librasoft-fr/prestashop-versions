<?php


namespace Opis\JsonSchema\Formats;

use Opis\JsonSchema\Uri;
use Opis\Uri\UriTemplate;

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