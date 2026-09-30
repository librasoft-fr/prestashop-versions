<?php

declare(strict_types=1);

namespace Sentry\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LogLevel;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\State\HubInterface;
use Sentry\State\Scope;


class LogToSentryIssueHandler extends AbstractProcessingHandler
{
    use CompatibilityProcessingHandlerTrait;

    private const CONTEXT_EXCEPTION_KEY = 'exception';

    
    private $hub;

    
    private $fillExtraContext;

    
    public function __construct(HubInterface $hub, $level = Logger::DEBUG, bool $bubble = true, bool $fillExtraContext = false)
    {
        $this->hub = $hub;
        $this->fillExtraContext = $fillExtraContext;

        parent::__construct($level, $bubble);
    }

    
    public function handle($record): bool
    {
        
        if (!$this->isHandling($record) || $this->hasThrowable($record)) {
            return false;
        }

        
        return parent::handle($record);
    }

    
    protected function doWrite($record): void
    {
        $event = Event::createEvent();
        $event->setLevel(self::getSeverityFromLevel($record['level']));
        $event->setMessage($record['message']);
        $event->setLogger(\sprintf('monolog.%s', $record['channel']));

        $hint = new EventHint();

        $this->hub->withScope(function (Scope $scope) use ($record, $event, $hint): void {
            $scope->setExtra('monolog.channel', $record['channel']);
            $scope->setExtra('monolog.level', $record['level_name']);

            if ($this->fillExtraContext) {
                $monologContextData = $this->getArrayFieldFromRecord($record, 'context');

                if ($monologContextData !== []) {
                    $scope->setExtra('monolog.context', $monologContextData);
                }

                $monologExtraData = $this->getArrayFieldFromRecord($record, 'extra');

                if ($monologExtraData !== []) {
                    $scope->setExtra('monolog.extra', $monologExtraData);
                }
            }

            $this->hub->captureEvent($event, $hint);
        });
    }

    
    private function hasThrowable($record): bool
    {
        $exception = $this->getArrayFieldFromRecord($record, 'context')[self::CONTEXT_EXCEPTION_KEY] ?? null;

        return $exception instanceof \Throwable;
    }

    
    private function getArrayFieldFromRecord($record, string $field): array
    {
        if (isset($record[$field]) && \is_array($record[$field])) {
            return $record[$field];
        }

        return [];
    }
}
