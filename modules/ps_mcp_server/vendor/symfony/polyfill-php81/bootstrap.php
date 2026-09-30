<?php



use Symfony\Polyfill\Php81 as p;

if (\PHP_VERSION_ID >= 80100) {
    return;
}

if (defined('MYSQLI_REFRESH_SLAVE') && !defined('MYSQLI_REFRESH_REPLICA')) {
    define('MYSQLI_REFRESH_REPLICA', 64);
}

if (\extension_loaded('curl') && !defined('CURLOPT_ISSUERCERT_BLOB') && curl_version()['version_number'] >= 0x074700) {
    define('CURLOPT_ISSUERCERT_BLOB', 40295);
}

if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool { return p\Php81::array_is_list($array); }
}

if (!function_exists('enum_exists')) {
    function enum_exists(string $enum, bool $autoload = true): bool { return $autoload && class_exists($enum) && false; }
}
