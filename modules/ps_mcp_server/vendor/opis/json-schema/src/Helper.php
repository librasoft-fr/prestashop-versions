<?php


namespace Opis\JsonSchema;

final class Helper
{
    
    public const JSON_TYPES = ['string', 'number', 'boolean', 'null', 'object', 'array'];

    
    public const JSON_SUBTYPES = ['integer' => 'number'];

    
    protected const PHP_TYPE_MAP = [
        'NULL' => 'null',
        'integer' => 'integer',
        'double' => 'number',
        'boolean' => 'boolean',
        'array' => 'array',
        'object' => 'object',
        'string' => 'string',
    ];

    
    public static function isValidJsonType(string $type): bool
    {
        if (isset(self::JSON_SUBTYPES[$type])) {
            return true;
        }

        return in_array($type, self::JSON_TYPES, true);
    }

    
    public static function getJsonSuperType(string $type): ?string
    {
        return self::JSON_SUBTYPES[$type] ?? null;
    }

    
    public static function getJsonType($value, bool $use_subtypes = true): ?string
    {
        $type = self::PHP_TYPE_MAP[gettype($value)] ?? null;
        if ($type === null) {
            return null;
        } elseif ($type === 'array') {
            return self::isIndexedArray($value) ? 'array' : null;
        }

        if ($use_subtypes) {
            if ($type === 'number' && self::isMultipleOf($value, 1)) {
                return 'integer';
            }
        } elseif ($type === 'integer') {
            return 'number';
        }

        return $type;
    }

    
    public static function jsonTypeMatches(string $type, $allowed): bool
    {
        if (!$allowed) {
            return false;
        }

        if (is_string($allowed)) {
            if ($type === $allowed) {
                return true;
            }

            return $allowed === self::getJsonSuperType($type);
        }

        if (is_array($allowed)) {
            if (in_array($type, $allowed, true)) {
                return true;
            }

            if ($type = self::getJsonSuperType($type)) {
                return in_array($type, $allowed, true);
            }
        }

        return false;
    }

    
    public static function valueIsOfJsonType($value, $type): bool
    {
        $t = self::getJsonType($value);
        if ($t === null) {
            return false;
        }

        return self::jsonTypeMatches($t, $type);
    }

    
    public static function isIndexedArray(array $array): bool
    {
        for ($i = 0, $max = count($array); $i < $max; $i++) {
            if (!array_key_exists($i, $array)) {
                return false;
            }
        }

        return true;
    }

    
    public static function convertAssocArrayToObject($schema)
    {
        if (is_null($schema) || is_scalar($schema)) {
            return $schema;
        }

        $keepArray = is_array($schema) && self::isIndexedArray($schema);

        $data = [];

        foreach ($schema as $key => $value) {
            $data[$key] = is_array($value) || is_object($value) ? self::convertAssocArrayToObject($value) : $value;
        }

        return $keepArray ? $data : (object) $data;
    }

    
    public static function equals($a, $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $type = self::getJsonType($a, false);
        if ($type === null || $type !== self::getJsonType($b, false)) {
            return false;
        }

        if ($type === 'number') {
            return $a == $b;
        }

        if ($type === "array") {
            $count = count($a);
            if ($count !== count($b)) {
                return false;
            }

            for ($i = 0; $i < $count; $i++) {
                if (!array_key_exists($i, $a) || !array_key_exists($i, $b)) {
                    return false;
                }
                if (!self::equals($a[$i], $b[$i])) {
                    return false;
                }
            }

            return true;
        }

        if ($type === "object") {
            $a = get_object_vars($a);
            if ($a === null) {
                return false;
            }

            $b = get_object_vars($b);
            if ($b === null) {
                return false;
            }

            if (count($a) !== count($b)) {
                return false;
            }

            foreach ($a as $prop => $value) {
                if (!array_key_exists($prop, $b)) {
                    return false;
                }
                if (!self::equals($value, $b[$prop])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }


    
    private static ?bool $hasBCMath = null;

    
    public static bool $useBCMath = true;

    
    public static int $numberScale = 14;

    
    public static function isMultipleOf($number, $divisor, ?int $scale = null): bool
    {
        if (!$number || $number == $divisor) {
            return true;
        }

        if ($divisor == 0) {
            return $number == 0;
        }

        
        if (!fmod($number, $divisor)) {
            return true;
        }

        $divisor = abs($divisor);
        if ($divisor == 1) {
            
            return false;
        }

        $number = abs($number);
        if ($divisor > $number) {
            
            return false;
        }

        
        $scale ??= self::$numberScale;

        if (
            !self::$useBCMath ||
            !(self::$hasBCMath ??= extension_loaded('bcmath'))
        ) {
            
            return abs($number - round($number / $divisor) * $divisor) < (10 ** -$scale);
        }

        

        $number = number_format($number, $scale, '.', '');
        $divisor = number_format($divisor, $scale, '.', '');

        
        if (!(float)$divisor) {
            return $number === $divisor;
        }

        $x = bcdiv($number, $divisor, 0);
        $x = bcmul($divisor, $x, $scale);
        $x = bcsub($number, $x, $scale);

        return 0 === bccomp($x, 0, $scale);
    }

    
    public static function cloneValue($value)
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(self::class . '::cloneValue', $value);
        }

        if (is_object($value)) {
            return (object)array_map(self::class . '::cloneValue', get_object_vars($value));
        }

        return null;
    }

    
    public static function isValidPattern(string $pattern): bool
    {
        if (strpos($pattern, '\Z') !== false) {
            return false;
        }

        return @preg_match("\x07{$pattern}\x07u", '') !== false;
    }

    
    public static function patternToRegex(string $pattern): string
    {
        return "\x07{$pattern}\x07uD";
    }

    
    public static function toJSON($data)
    {
        if ($data === null || is_scalar($data)) {
            return $data;
        }

        $map = [];

        $isArray = true;
        $index = 0;
        foreach ($data as $key => $value) {
            $map[$key] = self::toJSON($value);
            if ($isArray) {
                if ($index !== $key) {
                    $isArray = false;
                } else {
                    $index++;
                }
            }
        }

        if ($isArray) {
            if (!$map && is_object($data)) {
                return (object) $map;
            }
            return $map;
        }

        return (object) $map;
    }
}
