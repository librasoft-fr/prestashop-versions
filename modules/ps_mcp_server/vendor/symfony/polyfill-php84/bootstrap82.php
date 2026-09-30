<?php



use Symfony\Polyfill\Php84 as p;

if (\PHP_VERSION_ID >= 80400) {
    return;
}

if (extension_loaded('intl') && !function_exists('grapheme_str_split')) {
    function grapheme_str_split(string $string, int $length = 1): array|false { return p\Php84::grapheme_str_split($string, $length); }
}
