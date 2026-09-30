<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Monolog;

use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\LineFormatter;
use PsMcpServerDeps\Monolog\Handler\HandlerInterface;
use PsMcpServerDeps\Sentry\Logs\LogLevel;
use PsMcpServerDeps\Sentry\Logs\Logs;

class LogsHandler implements HandlerInterface
{
    use CompatibilityLogLevelTrait;

    private $logLevel;

    private $bubble;

    public function __construct($logLevel = null, bool $bubble = true)
    {
        $this->logLevel = $logLevel ?? LogLevel::debug();
        $this->bubble = $bubble;
    }

    public function isHandling($record): bool
    {
        if ($this->logLevel instanceof LogLevel) {
            return self::getSentryLogLevelFromMonologLevel($record['level'])->getPriority() >= $this->logLevel->getPriority();
        } elseif ($this->logLevel instanceof \PsMcpServerDeps\Monolog\Level) {
            return $record['level'] >= $this->logLevel->value;
        }

        return $record['level'] >= $this->logLevel;
    }

    public function handle($record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            return false;
        }

        Logs::getInstance()->aggregator()->add(
            self::getSentryLogLevelFromMonologLevel($record['level']),
            $record['message'],
            [],
            $this->compileAttributes($record)
        );

        return $this->bubble === false;
    }

    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    public function close(): void
    {
        Logs::getInstance()->flush();
    }

    public function pushProcessor($callback): void
    {
    }

    public function popProcessor()
    {
        throw new \LogicException('You tried to pop from an empty processor stack.');
    }

    public function setFormatter(FormatterInterface $formatter): void
    {
    }

    public function getFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Throwable $e) {
        }
    }

    protected function compileAttributes($record): array
    {
        return array_merge($record['context'], $record['extra'], ['sentry.origin' => 'auto.log.monolog']);
    }
}
