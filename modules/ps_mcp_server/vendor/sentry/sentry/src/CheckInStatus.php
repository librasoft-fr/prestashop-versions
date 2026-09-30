<?php

declare(strict_types=1);

namespace Sentry;


final class CheckInStatus implements \Stringable
{
    
    private $value;

    
    private static $instances = [];

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function ok(): self
    {
        return self::getInstance('ok');
    }

    public static function error(): self
    {
        return self::getInstance('error');
    }

    public static function inProgress(): self
    {
        return self::getInstance('in_progress');
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
