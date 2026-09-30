<?php

declare(strict_types=1);

namespace Sentry\State;

use Sentry\Breadcrumb;
use Sentry\CheckInStatus;
use Sentry\ClientInterface;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\EventId;
use Sentry\Integration\IntegrationInterface;
use Sentry\MonitorConfig;
use Sentry\SentrySdk;
use Sentry\Severity;
use Sentry\Tracing\Span;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;


final class HubAdapter implements HubInterface
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

    
    public function getClient(): ?ClientInterface
    {
        return SentrySdk::getCurrentHub()->getClient();
    }

    
    public function getLastEventId(): ?EventId
    {
        return SentrySdk::getCurrentHub()->getLastEventId();
    }

    
    public function pushScope(): Scope
    {
        return SentrySdk::getCurrentHub()->pushScope();
    }

    
    public function popScope(): bool
    {
        return SentrySdk::getCurrentHub()->popScope();
    }

    
    public function withScope(callable $callback)
    {
        return SentrySdk::getCurrentHub()->withScope($callback);
    }

    
    public function configureScope(callable $callback): void
    {
        SentrySdk::getCurrentHub()->configureScope($callback);
    }

    
    public function bindClient(ClientInterface $client): void
    {
        SentrySdk::getCurrentHub()->bindClient($client);
    }

    
    public function captureMessage(string $message, ?Severity $level = null, ?EventHint $hint = null): ?EventId
    {
        return SentrySdk::getCurrentHub()->captureMessage($message, $level, $hint);
    }

    
    public function captureException(\Throwable $exception, ?EventHint $hint = null): ?EventId
    {
        return SentrySdk::getCurrentHub()->captureException($exception, $hint);
    }

    
    public function captureEvent(Event $event, ?EventHint $hint = null): ?EventId
    {
        return SentrySdk::getCurrentHub()->captureEvent($event, $hint);
    }

    
    public function captureLastError(?EventHint $hint = null): ?EventId
    {
        return SentrySdk::getCurrentHub()->captureLastError($hint);
    }

    
    public function captureCheckIn(string $slug, CheckInStatus $status, $duration = null, ?MonitorConfig $monitorConfig = null, ?string $checkInId = null): ?string
    {
        return SentrySdk::getCurrentHub()->captureCheckIn($slug, $status, $duration, $monitorConfig, $checkInId);
    }

    
    public function addBreadcrumb(Breadcrumb $breadcrumb): bool
    {
        return SentrySdk::getCurrentHub()->addBreadcrumb($breadcrumb);
    }

    
    public function getIntegration(string $className): ?IntegrationInterface
    {
        return SentrySdk::getCurrentHub()->getIntegration($className);
    }

    
    public function startTransaction(TransactionContext $context, array $customSamplingContext = []): Transaction
    {
        return SentrySdk::getCurrentHub()->startTransaction($context, $customSamplingContext);
    }

    
    public function getTransaction(): ?Transaction
    {
        return SentrySdk::getCurrentHub()->getTransaction();
    }

    
    public function getSpan(): ?Span
    {
        return SentrySdk::getCurrentHub()->getSpan();
    }

    
    public function setSpan(?Span $span): HubInterface
    {
        return SentrySdk::getCurrentHub()->setSpan($span);
    }

    
    public function __clone()
    {
        throw new \BadMethodCallException('Cloning is forbidden.');
    }

    
    public function __wakeup(): void
    {
        throw new \BadMethodCallException('Unserializing instances of this class is forbidden.');
    }

    
    public function __sleep()
    {
        throw new \BadMethodCallException('Serializing instances of this class is forbidden.');
    }
}
