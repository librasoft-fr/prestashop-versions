<?php







use Symfony\Polyfill\Intl\Grapheme as p;

if (!function_exists('grapheme_stripos')) {
    function grapheme_stripos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $locale = ''): int|false { return p\Grapheme::grapheme_stripos((string) $haystack, (string) $needle, (int) $offset); }
}
if (!function_exists('grapheme_stristr')) {
    function grapheme_stristr(?string $haystack, ?string $needle, ?bool $beforeNeedle = false, ?string $locale = ''): string|false { return p\Grapheme::grapheme_stristr((string) $haystack, (string) $needle, (bool) $beforeNeedle); }
}
if (!function_exists('grapheme_strlen')) {
    function grapheme_strlen(?string $string): int|false|null { return p\Grapheme::grapheme_strlen((string) $string); }
}
if (!function_exists('grapheme_strpos')) {
    function grapheme_strpos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $locale = ''): int|false { return p\Grapheme::grapheme_strpos((string) $haystack, (string) $needle, (int) $offset); }
}
if (!function_exists('grapheme_strripos')) {
    function grapheme_strripos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $locale = ''): int|false { return p\Grapheme::grapheme_strripos((string) $haystack, (string) $needle, (int) $offset); }
}
if (!function_exists('grapheme_strrpos')) {
    function grapheme_strrpos(?string $haystack, ?string $needle, ?int $offset = 0, ?string $locale = ''): int|false { return p\Grapheme::grapheme_strrpos((string) $haystack, (string) $needle, (int) $offset); }
}
if (!function_exists('grapheme_strstr')) {
    function grapheme_strstr(?string $haystack, ?string $needle, ?bool $beforeNeedle = false, ?string $locale = ''): string|false { return p\Grapheme::grapheme_strstr((string) $haystack, (string) $needle, (bool) $beforeNeedle); }
}
if (!function_exists('grapheme_substr')) {
    function grapheme_substr(?string $string, ?int $offset, ?int $length = null, ?string $locale = ''): string|false { return p\Grapheme::grapheme_substr((string) $string, (int) $offset, $length); }
}
