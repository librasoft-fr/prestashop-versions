<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Monolog;

use PsMcpServerDeps\Monolog\Handler\AbstractProcessingHandler;
use PsMcpServerDeps\Monolog\Logger;
use PsMcpServerDeps\Sentry\Event;
use PsMcpServerDeps\Sentry\EventHint;
use PsMcpServerDeps\Sentry\State\HubInterface;
use PsMcpServerDeps\Sentry\State\Scope;

final class Handler extends AbstractProcessingHandler
{
    use CompatibilityProcessingHandlerTrait;

    private const CONTEXT_EXCEPTION_KEY = 'exception';

    private $hub;

    private $fillExtraContext;

    public function __construct(HubInterface $hub, $level = Logger::DEBUG, bool $bubble = true, bool $fillExtraContext = false)
    {
        parent::__construct($level, $bubble);

        $this->hub = $hub;
        $this->fillExtraContext = $fillExtraContext;
    }

    protected function doWrite($record): void
    {
        $event = Event::createEvent();
        $event->setLevel(self::getSeverityFromLevel($record['level']));
        $event->setMessage($record['message']);
        $event->setLogger(\sprintf('monolog.%s', $record['channel']));

        $hint = new EventHint();

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            $hint->exception = $record['context']['exception'];
        }

        $this->hub->withScope(function (Scope $scope) use ($record, $event, $hint): void {
            $scope->setExtra('monolog.channel', $record['channel']);
            $scope->setExtra('monolog.level', $record['level_name']);

            $monologContextData = $this->getMonologContextData($record['context']);

            if ($monologContextData !== []) {
                $scope->setExtra('monolog.context', $monologContextData);
            }

            $monologExtraData = $this->getMonologExtraData($record['extra']);

            if ($monologExtraData !== []) {
                $scope->setExtra('monolog.extra', $monologExtraData);
            }

            $this->hub->captureEvent($event, $hint);
        });
    }

    private function getMonologContextData(array $context): array
    {
        if (!$this->fillExtraContext) {
            return [];
        }

        $contextData = [];

        foreach ($context as $key => $value) {
            if ($key === self::CONTEXT_EXCEPTION_KEY) {
                continue;
            }

            $contextData[$key] = $value;
        }

        return $contextData;
    }

    private function getMonologExtraData(array $context): array
    {
        if (!$this->fillExtraContext) {
            return [];
        }

        $extraData = [];

        foreach ($context as $key => $value) {
            $extraData[$key] = $value;
        }

        return $extraData;
    }
}
