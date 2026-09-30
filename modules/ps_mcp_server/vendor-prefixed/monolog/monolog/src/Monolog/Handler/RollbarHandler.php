<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Logger;
use Rollbar\RollbarLogger;

class RollbarHandler extends AbstractProcessingHandler
{
    protected $rollbarLogger;

    protected $levelMap = [
        Logger::DEBUG => 'debug',
        Logger::INFO => 'info',
        Logger::NOTICE => 'info',
        Logger::WARNING => 'warning',
        Logger::ERROR => 'error',
        Logger::CRITICAL => 'critical',
        Logger::ALERT => 'critical',
        Logger::EMERGENCY => 'critical',
    ];

    private $hasRecords = false;

    protected $initialized = false;

    public function __construct(RollbarLogger $rollbarLogger, $level = Logger::ERROR, bool $bubble = true)
    {
        $this->rollbarLogger = $rollbarLogger;

        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        if (!$this->initialized) {
            register_shutdown_function([$this, 'close']);
            $this->initialized = true;
        }

        $context = $record['context'];
        $context = array_merge($context, $record['extra'], [
            'level' => $this->levelMap[$record['level']],
            'monolog_level' => $record['level_name'],
            'channel' => $record['channel'],
            'datetime' => $record['datetime']->format('U'),
        ]);

        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $exception = $context['exception'];
            unset($context['exception']);
            $toLog = $exception;
        } else {
            $toLog = $record['message'];
        }

        $this->rollbarLogger->log($context['level'], $toLog, $context);

        $this->hasRecords = true;
    }

    public function flush(): void
    {
        if ($this->hasRecords) {
            $this->rollbarLogger->flush();
            $this->hasRecords = false;
        }
    }

    public function close(): void
    {
        $this->flush();
    }

    public function reset()
    {
        $this->flush();

        parent::reset();
    }
}
