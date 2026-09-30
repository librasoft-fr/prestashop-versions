<?php



use Symfony\Polyfill\Php84 as p;

if (\PHP_VERSION_ID >= 80400) {
    return;
}

if (\extension_loaded('curl')) {
    
    if (defined('CURL_VERSION_HTTP3') || \PHP_VERSION_ID < 80200 && curl_version()['version_number'] >= 0x074200) {
        if (!defined('CURL_HTTP_VERSION_3')) {
            define('CURL_HTTP_VERSION_3', 30);
        }

        
        if (!defined('CURL_HTTP_VERSION_3ONLY') && curl_version()['version_number'] >= 0x075800) {
            define('CURL_HTTP_VERSION_3ONLY', 31);
        }
    }
}

if (!function_exists('array_find')) {
    function array_find(array $array, callable $callback) { return p\Php84::array_find($array, $callback); }
}

if (!function_exists('array_find_key')) {
    function array_find_key(array $array, callable $callback) { return p\Php84::array_find_key($array, $callback); }
}

if (!function_exists('array_any')) {
    function array_any(array $array, callable $callback): bool { return p\Php84::array_any($array, $callback); }
}

if (!function_exists('array_all')) {
    function array_all(array $array, callable $callback): bool { return p\Php84::array_all($array, $callback); }
}

if (!function_exists('fpow')) {
    function fpow(float $num, float $exponent): float { return p\Php84::fpow($num, $exponent); }
}

if (\PHP_VERSION_ID < 80000) {
    require __DIR__.'/bootstrap72.php';
}

if (extension_loaded('mbstring')) {
    if (!function_exists('mb_ucfirst')) {
        function mb_ucfirst(?string $string, ?string $encoding = null): string { return p\Php84::mb_ucfirst((string) $string, $encoding); }
    }

    if (!function_exists('mb_lcfirst')) {
        function mb_lcfirst(?string $string, ?string $encoding = null): string { return p\Php84::mb_lcfirst((string) $string, $encoding); }
    }

    if (!function_exists('mb_trim')) {
        function mb_trim(?string $string, ?string $characters = null, ?string $encoding = null): string { return p\Php84::mb_trim((string) $string, $characters, $encoding); }
    }

    if (!function_exists('mb_ltrim')) {
        function mb_ltrim(?string $string, ?string $characters = null, ?string $encoding = null): string { return p\Php84::mb_ltrim((string) $string, $characters, $encoding); }
    }

    if (!function_exists('mb_rtrim')) {
        function mb_rtrim(?string $string, ?string $characters = null, ?string $encoding = null): string { return p\Php84::mb_rtrim((string) $string, $characters, $encoding); }
    }
}

if (extension_loaded('bcmath')) {
    if (!function_exists('bcceil')) {
        function bcceil(string $num): string { return p\Php84::bcceil($num); }
    }
    if (!function_exists('bcdivmod')) {
        function bcdivmod(string $num1, string $num2, ?int $scale = null): ?array { return p\Php84::bcdivmod($num1, $num2, $scale); }
    }
    if (!function_exists('bcfloor')) {
        function bcfloor(string $num): string { return p\Php84::bcfloor($num); }
    }
    if (!function_exists('bcround')) {
        function bcround(string $num, int $precision = 0, $mode = RoundingMode::HalfAwayFromZero): string { return p\Php84::bcround($num, $precision, $mode); }
    }
}

if (\PHP_VERSION_ID >= 80200) {
    return require __DIR__.'/bootstrap82.php';
}

if (extension_loaded('intl') && !function_exists('grapheme_str_split')) {
    function grapheme_str_split(string $string, int $length = 1) { return p\Php84::grapheme_str_split($string, $length); }
}
