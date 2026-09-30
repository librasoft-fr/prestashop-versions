<?php

declare(strict_types=1);

namespace Sentry;


final class Severity implements \Stringable
{
    
    public const DEBUG = 'debug';

    
    public const INFO = 'info';

    
    public const WARNING = 'warning';

    
    public const ERROR = 'error';

    
    public const FATAL = 'fatal';

    
    public const ALLOWED_SEVERITIES = [
        self::DEBUG,
        self::INFO,
        self::WARNING,
        self::ERROR,
        self::FATAL,
    ];

    
    private $value;

    
    public function __construct(string $value = self::INFO)
    {
        if (!\in_array($value, self::ALLOWED_SEVERITIES, true)) {
            throw new \InvalidArgumentException(\sprintf('The "%s" is not a valid enum value.', $value));
        }

        $this->value = $value;
    }

    
    public static function fromError(int $severity): self
    {
        switch ($severity) {
            case \E_DEPRECATED:
            case \E_USER_DEPRECATED:
            case \E_WARNING:
            case \E_USER_WARNING:
                return self::warning();
            case \E_ERROR:
            case \E_PARSE:
            case \E_CORE_ERROR:
            case \E_CORE_WARNING:
            case \E_COMPILE_ERROR:
            case \E_COMPILE_WARNING:
                return self::fatal();
            case \E_RECOVERABLE_ERROR:
            case \E_USER_ERROR:
                return self::error();
            case \E_NOTICE:
            case \E_USER_NOTICE:
            case 2048: 
                return self::info();
            default:
                return self::error();
        }
    }

    
    public static function debug(): self
    {
        return new self(self::DEBUG);
    }

    
    public static function info(): self
    {
        return new self(self::INFO);
    }

    
    public static function warning(): self
    {
        return new self(self::WARNING);
    }

    
    public static function error(): self
    {
        return new self(self::ERROR);
    }

    
    public static function fatal(): self
    {
        return new self(self::FATAL);
    }

    
    public function isEqualTo(self $other): bool
    {
        return $this->value === (string) $other;
    }

    
    public function __toString(): string
    {
        return $this->value;
    }
}
