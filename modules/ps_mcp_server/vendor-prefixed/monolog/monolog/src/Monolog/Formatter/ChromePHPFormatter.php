<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Formatter;

use PsMcpServerDeps\Monolog\Logger;

class ChromePHPFormatter implements FormatterInterface
{
    private $logLevels = [
        Logger::DEBUG => 'log',
        Logger::INFO => 'info',
        Logger::NOTICE => 'info',
        Logger::WARNING => 'warn',
        Logger::ERROR => 'error',
        Logger::CRITICAL => 'error',
        Logger::ALERT => 'error',
        Logger::EMERGENCY => 'error',
    ];

    public function format(array $record)
    {
        $backtrace = 'unknown';
        if (isset($record['extra']['file'], $record['extra']['line'])) {
            $backtrace = $record['extra']['file'] . ' : ' . $record['extra']['line'];
            unset($record['extra']['file'], $record['extra']['line']);
        }

        $message = ['message' => $record['message']];
        if ($record['context']) {
            $message['context'] = $record['context'];
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
        }
        if (count($message) === 1) {
            $message = reset($message);
        }

        return [
            $record['channel'],
            $message,
            $backtrace,
            $this->logLevels[$record['level']],
        ];
    }

    public function formatBatch(array $records)
    {
        $formatted = [];

        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }

        return $formatted;
    }
}
