<?php


namespace Opis\JsonSchema\Filters;

final class CommonFilters
{
    public static function Regex(string $value, array $args): bool
    {
        if (!isset($args['pattern']) || !is_string($args['pattern'])) {
            return false;
        }

        return (bool)preg_match($args['pattern'], $value);
    }

    public static function Equals($value, array $args): bool
    {
        if (!array_key_exists('value', $args)) {
            return false;
        }

        if ($args['strict'] ?? false) {
            return $value === $args['value'];
        }

        return $value == $args['value'];
    }
}