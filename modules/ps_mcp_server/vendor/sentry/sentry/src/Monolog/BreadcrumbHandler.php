<?php

declare(strict_types=1);

namespace Sentry\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LogLevel;
use Sentry\Breadcrumb;
use Sentry\Event;
use Sentry\State\HubInterface;
use Sentry\State\Scope;


final class BreadcrumbHandler extends AbstractProcessingHandler
{
    
    private $hub;

    
    public function __construct(HubInterface $hub, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->hub = $hub;

        parent::__construct($level, $bubble);
    }

    
    protected function write($record): void
    {
        $datetime = $record['datetime'] ?? null;
        $timestamp = $datetime instanceof \DateTimeInterface
            ? $datetime->getTimestamp() + (int) $datetime->format('u') / 1000000
            : null;

        $breadcrumb = new Breadcrumb(
            $this->getBreadcrumbLevel($record['level']),
            $this->getBreadcrumbType($record['level']),
            $record['channel'],
            $record['message'],
            ($record['context'] ?? []) + ($record['extra'] ?? []),
            $timestamp
        );

        $this->hub->addBreadcrumb($breadcrumb);
    }

    
    private function getBreadcrumbLevel($level): string
    {
        if ($level instanceof Level) {
            $level = $level->value;
        }

        switch ($level) {
            case Logger::DEBUG:
                return Breadcrumb::LEVEL_DEBUG;
            case Logger::INFO:
            case Logger::NOTICE:
                return Breadcrumb::LEVEL_INFO;
            case Logger::WARNING:
                return Breadcrumb::LEVEL_WARNING;
            case Logger::ERROR:
                return Breadcrumb::LEVEL_ERROR;
            default:
                return Breadcrumb::LEVEL_FATAL;
        }
    }

    private function getBreadcrumbType(int $level): string
    {
        if ($level >= Logger::ERROR) {
            return Breadcrumb::TYPE_ERROR;
        }

        return Breadcrumb::TYPE_DEFAULT;
    }
}
