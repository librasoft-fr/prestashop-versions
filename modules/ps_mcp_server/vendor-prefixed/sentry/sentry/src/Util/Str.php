<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Util;

class Str
{
    public static function vsprintfOrNull(string $message, array $values): ?string
    {
        if (empty($values)) {
            return $message;
        }

        foreach ($values as $value) {
            if (!\is_scalar($value) && $value !== null) {
                return null;
            }
        }

        try {
            $result = @vsprintf($message, $values);

            return $result === false ? null : $result;
        } catch (\Error $e) {
            return null;
        }
    }
}
