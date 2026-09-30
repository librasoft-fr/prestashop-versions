<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\NormalizerFormatter;
use PsMcpServerDeps\Monolog\Logger;

class ZendMonitorHandler extends AbstractProcessingHandler
{
    protected $levelMap = [];

    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        if (!function_exists('zend_monitor_custom_event')) {
            throw new MissingExtensionException('You must have Zend Server installed with Zend Monitor enabled in order to use this handler');
        }

        $this->levelMap = [
            Logger::DEBUG => \ZEND_MONITOR_EVENT_SEVERITY_INFO,
            Logger::INFO => \ZEND_MONITOR_EVENT_SEVERITY_INFO,
            Logger::NOTICE => \ZEND_MONITOR_EVENT_SEVERITY_INFO,
            Logger::WARNING => \ZEND_MONITOR_EVENT_SEVERITY_WARNING,
            Logger::ERROR => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
            Logger::CRITICAL => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
            Logger::ALERT => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
            Logger::EMERGENCY => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
        ];
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        $this->writeZendMonitorCustomEvent(
            Logger::getLevelName($record['level']),
            $record['message'],
            $record['formatted'],
            $this->levelMap[$record['level']]
        );
    }

    protected function writeZendMonitorCustomEvent(string $type, string $message, array $formatted, int $severity): void
    {
        zend_monitor_custom_event($type, $message, $formatted, $severity);
    }

    public function getDefaultFormatter(): FormatterInterface
    {
        return new NormalizerFormatter();
    }

    public function getLevelMap(): array
    {
        return $this->levelMap;
    }
}
