<?php


namespace Opis\JsonSchema\Formats;

use Throwable;
use Opis\JsonSchema\Uri;

class IriFormats
{
    private const SKIP = [0x23, 0x26, 0x2F, 0x3A, 0x3D, 0x3F, 0x40, 0x5B, 0x5C, 0x5D];

    
    private static $idn = false;

    
    public static function iri(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        try {
            $components = Uri::parseComponents(Uri::encodeComponent($value, self::SKIP), true, true);
        } catch (Throwable $e) {
            return false;
        }

        return isset($components['scheme']) && $components['scheme'] !== '';
    }

    
    public static function iriReference(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        try {
            return Uri::parseComponents(Uri::encodeComponent($value, self::SKIP), true, true) !== null;
        } catch (Throwable $e) {
            return false;
        }
    }

    
    public static function idnHostname(string $value, ?callable $idn = null): bool
    {
        $idn = $idn ?? static::idn();

        if ($idn) {
            $value = $idn($value);
            if ($value === null) {
                return false;
            }
        }

        return Uri::isValidHost($value);
    }

    
    public static function idnEmail(string $value, ?callable $idn = null): bool
    {
        $idn = $idn ?? static::idn();

        if ($idn) {
            if (!preg_match('/^(?<name>.+)@(?<domain>.+)$/u', $value, $m)) {
                return false;
            }

            $m['name'] = $idn($m['name']);
            if ($m['name'] === null) {
                return false;
            }

            $m['domain'] = $idn($m['domain']);
            if ($m['domain'] === null) {
                return false;
            }

            $value = $m['name'] . '@' . $m['domain'];
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    
    public static function idn(): ?callable
    {
        if (static::$idn === false) {
            if (function_exists('idn_to_ascii')) {
                static::$idn = static function (string $value): ?string {
                    
                    $value = idn_to_ascii($value, 0, INTL_IDNA_VARIANT_UTS46);

                    return is_string($value) ? $value : null;
                };
            } else {
                static::$idn = null;
            }
        }

        return static::$idn;
    }
}