<?php

declare(strict_types=1);

namespace Sentry\Monolog;

use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Sentry\Severity;

if (Logger::API >= 3) {
    
    trait CompatibilityProcessingHandlerTrait
    {
        
        abstract protected function doWrite($record): void;

        
        protected function write(LogRecord $record): void
        {
            $this->doWrite($record);
        }

        
        private static function getSeverityFromLevel(int $level): Severity
        {
            $level = Level::from($level);

            switch ($level) {
                case Level::Debug:
                    return Severity::debug();
                case Level::Warning:
                    return Severity::warning();
                case Level::Error:
                    return Severity::error();
                case Level::Critical:
                case Level::Alert:
                case Level::Emergency:
                    return Severity::fatal();
                case Level::Info:
                case Level::Notice:
                default:
                    return Severity::info();
            }
        }
    }
} else {
    
    trait CompatibilityProcessingHandlerTrait
    {
        
        abstract protected function doWrite($record): void;

        
        protected function write(array $record): void
        {
            $this->doWrite($record);
        }

        
        private static function getSeverityFromLevel(int $level): Severity
        {
            switch ($level) {
                case Logger::DEBUG:
                    return Severity::debug();
                case Logger::WARNING:
                    return Severity::warning();
                case Logger::ERROR:
                    return Severity::error();
                case Logger::CRITICAL:
                case Logger::ALERT:
                case Logger::EMERGENCY:
                    return Severity::fatal();
                case Logger::INFO:
                case Logger::NOTICE:
                default:
                    return Severity::info();
            }
        }
    }
}
