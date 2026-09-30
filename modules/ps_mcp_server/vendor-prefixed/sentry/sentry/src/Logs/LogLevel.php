<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Logs;

class LogLevel
{
    private $value;

    private $priority;

    private static $instances = [];

    private function __construct(string $value, int $priority)
    {
        $this->value = $value;
        $this->priority = $priority;
    }

    public static function trace(): self
    {
        return self::getInstance('trace', 10);
    }

    public static function debug(): self
    {
        return self::getInstance('debug', 20);
    }

    public static function info(): self
    {
        return self::getInstance('info', 30);
    }

    public static function warn(): self
    {
        return self::getInstance('warn', 40);
    }

    public static function error(): self
    {
        return self::getInstance('error', 50);
    }

    public static function fatal(): self
    {
        return self::getInstance('fatal', 60);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function toPsrLevel(): string
    {
        switch ($this->value) {
            case 'trace':
            case 'debug':
                return \Psr\Log\LogLevel::DEBUG;
            case 'warn':
                return \Psr\Log\LogLevel::WARNING;
            case 'error':
                return \Psr\Log\LogLevel::ERROR;
            case 'fatal':
                return \Psr\Log\LogLevel::CRITICAL;
            case 'info':
            default:
                return \Psr\Log\LogLevel::INFO;
        }
    }

    private static function getInstance(string $value, int $priority): self
    {
        if (!isset(self::$instances[$value])) {
            self::$instances[$value] = new self($value, $priority);
        }

        return self::$instances[$value];
    }
}
