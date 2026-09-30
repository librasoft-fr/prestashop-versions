<?php

declare(strict_types=1);

namespace Sentry\Util;

use Sentry\Client;
use Sentry\Dsn;


final class Http
{
    public static function getSentryAuthHeader(Dsn $dsn, string $sdkIdentifier, string $sdkVersion): string
    {
        $authHeader = [
            'sentry_version=' . Client::PROTOCOL_VERSION,
            'sentry_client=' . $sdkIdentifier . '/' . $sdkVersion,
            'sentry_key=' . $dsn->getPublicKey(),
        ];

        return 'Sentry ' . implode(', ', $authHeader);
    }

    
    public static function getRequestHeaders(Dsn $dsn, string $sdkIdentifier, string $sdkVersion): array
    {
        return [
            'Content-Type: application/x-sentry-envelope',
            'X-Sentry-Auth: ' . self::getSentryAuthHeader($dsn, $sdkIdentifier, $sdkVersion),
        ];
    }

    
    public static function parseResponseHeaders(string $headerLine, array &$headers): int
    {
        if (strpos($headerLine, ':') === false) {
            return \strlen($headerLine);
        }

        [$name, $value] = explode(':', trim($headerLine), 2);

        $name = trim($name);
        $value = trim($value);

        if (isset($headers[$name])) {
            $headers[$name][] = $value;
        } else {
            $headers[$name] = (array) $value;
        }

        return \strlen($headerLine);
    }
}
