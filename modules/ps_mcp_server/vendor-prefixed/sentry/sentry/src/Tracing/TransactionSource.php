<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Tracing;

final class TransactionSource implements \Stringable
{
    private $value;

    private static $instances = [];

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function custom(): self
    {
        return self::getInstance('custom');
    }

    public static function url(): self
    {
        return self::getInstance('url');
    }

    public static function route(): self
    {
        return self::getInstance('route');
    }

    public static function view(): self
    {
        return self::getInstance('view');
    }

    public static function component(): self
    {
        return self::getInstance('component');
    }

    public static function task(): self
    {
        return self::getInstance('task');
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
