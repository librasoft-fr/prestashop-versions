<?php

declare(strict_types=1);

namespace Sentry\Logs;

use Sentry\EventId;
use Sentry\SentrySdk;

class Logs
{
    
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    
    public function trace(string $message, array $values = [], array $attributes = []): void
    {
        $this->aggregator()->add(LogLevel::trace(), $message, $values, $attributes);
    }

    
    public function debug(string $message, array $values = [], array $attributes = []): void
    {
        $this->aggregator()->add(LogLevel::debug(), $message, $values, $attributes);
    }

    
    public function info(string $message, array $values = [], array $attributes = []): void
    {
        $this->aggregator()->add(LogLevel::info(), $message, $values, $attributes);
    }

    
    public function warn(string $message, array $values = [], array $attributes = []): void
    {
        $this->aggregator()->add(LogLevel::warn(), $message, $values, $attributes);
    }

    
    public function error(string $message, array $values = [], array $attributes = []): void
    {
        $this->aggregator()->add(LogLevel::error(), $message, $values, $attributes);
    }

    
    public function fatal(string $message, array $values = [], array $attributes = []): void
    {
        $this->aggregator()->add(LogLevel::fatal(), $message, $values, $attributes);
    }

    
    public function flush(): ?EventId
    {
        return $this->aggregator()->flush();
    }

    
    public function aggregator(): LogsAggregator
    {
        return SentrySdk::getCurrentRuntimeContext()->getLogsAggregator();
    }
}
