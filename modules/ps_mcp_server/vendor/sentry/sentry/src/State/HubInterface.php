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
use Sentry\Severity;
use Sentry\Tracing\Span;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;

interface HubInterface
{
    
    public function getClient(): ?ClientInterface;

    
    public function getLastEventId(): ?EventId;

    
    public function pushScope(): Scope;

    
    public function popScope(): bool;

    
    public function withScope(callable $callback);

    
    public function configureScope(callable $callback): void;

    
    public function bindClient(ClientInterface $client): void;

    
    public function captureMessage(string $message, ?Severity $level = null, ?EventHint $hint = null): ?EventId;

    
    public function captureException(\Throwable $exception, ?EventHint $hint = null): ?EventId;

    
    public function captureEvent(Event $event, ?EventHint $hint = null): ?EventId;

    
    public function captureLastError(?EventHint $hint = null): ?EventId;

    
    public function addBreadcrumb(Breadcrumb $breadcrumb): bool;

    
    public function captureCheckIn(string $slug, CheckInStatus $status, $duration = null, ?MonitorConfig $monitorConfig = null, ?string $checkInId = null): ?string;

    
    public function getIntegration(string $className): ?IntegrationInterface;

    
    public function startTransaction(TransactionContext $context, array $customSamplingContext = []): Transaction;

    
    public function getTransaction(): ?Transaction;

    
    public function getSpan(): ?Span;

    
    public function setSpan(?Span $span): HubInterface;
}
