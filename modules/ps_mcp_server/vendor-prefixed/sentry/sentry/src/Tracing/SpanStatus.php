<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

final class SpanStatus implements \Stringable
{
    private $value;

    private static $instances = [];

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function unauthenticated(): self
    {
        return self::getInstance('unauthenticated');
    }

    public static function permissionDenied(): self
    {
        return self::getInstance('permission_denied');
    }

    public static function notFound(): self
    {
        return self::getInstance('not_found');
    }

    public static function alreadyExists(): self
    {
        return self::getInstance('already_exists');
    }

    public static function failedPrecondition(): self
    {
        return self::getInstance('failed_precondition');
    }

    public static function resourceExhausted(): self
    {
        return self::getInstance('resource_exhausted');
    }

    public static function resourceExchausted(): self
    {
        return self::resourceExhausted();
    }

    public static function unimplemented(): self
    {
        return self::getInstance('unimplemented');
    }

    public static function unavailable(): self
    {
        return self::getInstance('unavailable');
    }

    public static function deadlineExceeded(): self
    {
        return self::getInstance('deadline_exceeded');
    }

    public static function ok(): self
    {
        return self::getInstance('ok');
    }

    public static function invalidArgument(): self
    {
        return self::getInstance('invalid_argument');
    }

    public static function internalError(): self
    {
        return self::getInstance('internal_error');
    }

    public static function unknownError(): self
    {
        return self::getInstance('unknown_error');
    }

    public static function createFromHttpStatusCode(int $statusCode): self
    {
        switch (true) {
            case $statusCode === 401:
                return self::unauthenticated();
            case $statusCode === 403:
                return self::permissionDenied();
            case $statusCode === 404:
                return self::notFound();
            case $statusCode === 409:
                return self::alreadyExists();
            case $statusCode === 413:
                return self::failedPrecondition();
            case $statusCode === 429:
                return self::resourceExhausted();
            case $statusCode === 501:
                return self::unimplemented();
            case $statusCode === 503:
                return self::unavailable();
            case $statusCode === 504:
                return self::deadlineExceeded();
            case $statusCode < 400:
                return self::ok();
            case $statusCode < 500:
                return self::invalidArgument();
            case $statusCode < 600:
                return self::internalError();
            default:
                return self::unknownError();
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function getInstance(string $value): self
    {
        if (!isset(self::$instances[$value])) {
            self::$instances[$value] = new self($value);
        }

        return self::$instances[$value];
    }
}
