<?php



use Symfony\Polyfill\Intl\Normalizer as p;

if (!function_exists('normalizer_is_normalized')) {
    function normalizer_is_normalized(?string $string, ?int $form = p\Normalizer::FORM_C): bool { return p\Normalizer::isNormalized((string) $string, (int) $form); }
}
if (!function_exists('normalizer_normalize')) {
    function normalizer_normalize(?string $string, ?int $form = p\Normalizer::FORM_C): string|false { return p\Normalizer::normalize((string) $string, (int) $form); }
}
if (!function_exists('normalizer_get_raw_decomposition')) {
    function normalizer_get_raw_decomposition(?string $string, ?int $form = p\Normalizer::FORM_C): ?string { return p\Normalizer::getRawDecomposition((string) $string, (int) $form); }
}
