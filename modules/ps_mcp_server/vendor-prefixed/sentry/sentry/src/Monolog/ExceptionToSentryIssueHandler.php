<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Monolog;

use PsMcpServerDeps\Monolog\Handler\AbstractHandler;
use PsMcpServerDeps\Monolog\Logger;
use PsMcpServerDeps\Sentry\State\HubInterface;
use PsMcpServerDeps\Sentry\State\Scope;

class ExceptionToSentryIssueHandler extends AbstractHandler
{
    private $hub;

    public function __construct(HubInterface $hub, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->hub = $hub;

        parent::__construct($level, $bubble);
    }

    public function handle($record): bool
    {
        $exception = $this->getExceptionFromRecord($record);

        if ($exception === null || !$this->isHandling($record)) {
            return false;
        }

        $this->hub->withScope(function (Scope $scope) use ($record, $exception): void {
            $scope->setExtra('monolog.channel', $record['channel']);
            $scope->setExtra('monolog.level', $record['level_name']);
            $scope->setExtra('monolog.message', $record['message']);

            $monologContextData = $this->getMonologContextData($this->getContextFromRecord($record));

            if ($monologContextData !== []) {
                $scope->setExtra('monolog.context', $monologContextData);
            }

            $monologExtraData = $this->getExtraFromRecord($record);

            if ($monologExtraData !== []) {
                $scope->setExtra('monolog.extra', $monologExtraData);
            }

            $this->hub->captureException($exception);
        });

        return $this->bubble === false;
    }

    private function getExceptionFromRecord($record): ?\Throwable
    {
        $exception = $this->getContextFromRecord($record)['exception'] ?? null;

        if ($exception instanceof \Throwable) {
            return $exception;
        }

        return null;
    }

    private function getContextFromRecord($record): array
    {
        return $this->getArrayFieldFromRecord($record, 'context');
    }

    private function getExtraFromRecord($record): array
    {
        return $this->getArrayFieldFromRecord($record, 'extra');
    }

    private function getArrayFieldFromRecord($record, string $field): array
    {
        if (isset($record[$field]) && \is_array($record[$field])) {
            return $record[$field];
        }

        return [];
    }

    private function getMonologContextData(array $context): array
    {
        unset($context['exception']);

        return $context;
    }
}
