<?php

declare(strict_types=1);

namespace Sentry;

use Psr\Log\LoggerInterface;
use Sentry\HttpClient\HttpClientInterface;
use Sentry\Integration\IntegrationInterface;
use Sentry\Integration\OTLPIntegration;
use Sentry\Logs\Logs;
use Sentry\Metrics\Metrics;
use Sentry\Metrics\TraceMetrics;
use Sentry\State\Scope;
use Sentry\Tracing\PropagationContext;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Sentry\Transport\TransportInterface;


function init(array $options = []): void
{
    $client = ClientBuilder::create($options)->getClient();

    SentrySdk::init()->bindClient($client);
}


function captureMessage(string $message, ?Severity $level = null, ?EventHint $hint = null): ?EventId
{
    return SentrySdk::getCurrentHub()->captureMessage($message, $level, $hint);
}


function captureException(\Throwable $exception, ?EventHint $hint = null): ?EventId
{
    return SentrySdk::getCurrentHub()->captureException($exception, $hint);
}


function captureEvent(Event $event, ?EventHint $hint = null): ?EventId
{
    return SentrySdk::getCurrentHub()->captureEvent($event, $hint);
}


function captureLastError(?EventHint $hint = null): ?EventId
{
    return SentrySdk::getCurrentHub()->captureLastError($hint);
}


function captureCheckIn(string $slug, CheckInStatus $status, $duration = null, ?MonitorConfig $monitorConfig = null, ?string $checkInId = null): ?string
{
    return SentrySdk::getCurrentHub()->captureCheckIn($slug, $status, $duration, $monitorConfig, $checkInId);
}


function withMonitor(string $slug, callable $callback, ?MonitorConfig $monitorConfig = null)
{
    $checkInId = SentrySdk::getCurrentHub()->captureCheckIn($slug, CheckInStatus::inProgress(), null, $monitorConfig);

    $status = CheckInStatus::ok();
    $duration = 0;

    try {
        $start = microtime(true);
        $result = $callback();
        $duration = microtime(true) - $start;

        return $result;
    } catch (\Throwable $e) {
        $status = CheckInStatus::error();

        throw $e;
    } finally {
        SentrySdk::getCurrentHub()->captureCheckIn($slug, $status, $duration, $monitorConfig, $checkInId);
    }
}


function addBreadcrumb($category, ?string $message = null, array $metadata = [], string $level = Breadcrumb::LEVEL_INFO, string $type = Breadcrumb::TYPE_DEFAULT, ?float $timestamp = null): void
{
    SentrySdk::getCurrentHub()->addBreadcrumb(
        $category instanceof Breadcrumb
            ? $category
            : new Breadcrumb($level, $type, $category, $message, $metadata, $timestamp)
    );
}


function configureScope(callable $callback): void
{
    SentrySdk::getCurrentHub()->configureScope($callback);
}


function withScope(callable $callback)
{
    return SentrySdk::getCurrentHub()->withScope($callback);
}

function startContext(): void
{
    SentrySdk::startContext();
}

function endContext(?int $timeout = null): void
{
    SentrySdk::endContext($timeout);
}


function withContext(callable $callback, ?int $timeout = null)
{
    return SentrySdk::withContext($callback, $timeout);
}


function startTransaction(TransactionContext $context, array $customSamplingContext = []): Transaction
{
    return SentrySdk::getCurrentHub()->startTransaction($context, $customSamplingContext);
}


function trace(callable $trace, SpanContext $context)
{
    return SentrySdk::getCurrentHub()->withScope(static function (Scope $scope) use ($context, $trace) {
        $parentSpan = $scope->getSpan();
        $span = null;

        
        
        
        if ($parentSpan !== null && $parentSpan->getSampled()) {
            $span = $parentSpan->startChild($context);

            $scope->setSpan($span);
        }

        try {
            return $trace($scope);
        } finally {
            if ($span !== null) {
                $span->finish();

                $scope->setSpan($parentSpan);
            }
        }
    });
}


function getOtlpTracesEndpointUrl(): ?string
{
    $hub = SentrySdk::getCurrentHub();
    $client = $hub->getClient();

    if ($client === null) {
        return null;
    }

    $integration = $hub->getIntegration(OTLPIntegration::class);
    if ($integration instanceof OTLPIntegration && $integration->getCollectorUrl() !== null) {
        return $integration->getCollectorUrl();
    }

    $dsn = $client->getOptions()->getDsn();
    if ($dsn === null) {
        return null;
    }

    return $dsn->getOtlpTracesEndpointUrl();
}


function getTraceparent(): string
{
    $hub = SentrySdk::getCurrentHub();
    $client = $hub->getClient();

    if ($client !== null) {
        $options = $client->getOptions();

        if ($options->isTracingEnabled()) {
            $span = SentrySdk::getCurrentHub()->getSpan();
            if ($span !== null) {
                return $span->toTraceparent();
            }
        }
    }

    $traceParent = '';
    $hub->configureScope(static function (Scope $scope) use (&$traceParent) {
        if ($scope->hasExternalPropagationContext()) {
            return;
        }

        $traceParent = $scope->getPropagationContext()->toTraceparent();
    });

    return $traceParent;
}


function getW3CTraceparent(): string
{
    return '';
}


function getBaggage(): string
{
    $hub = SentrySdk::getCurrentHub();
    $client = $hub->getClient();

    if ($client !== null) {
        $options = $client->getOptions();

        if ($options->isTracingEnabled()) {
            $span = SentrySdk::getCurrentHub()->getSpan();
            if ($span !== null) {
                return $span->toBaggage();
            }
        }
    }

    $baggage = '';
    $hub->configureScope(static function (Scope $scope) use (&$baggage) {
        if ($scope->hasExternalPropagationContext()) {
            return;
        }

        $baggage = $scope->getPropagationContext()->toBaggage();
    });

    return $baggage;
}


function continueTrace(string $sentryTrace, string $baggage): TransactionContext
{
    
    
    
    
    $transactionContext = TransactionContext::fromHeaders($sentryTrace, $baggage);
    $propagationContext = PropagationContext::fromDefaults();
    $metadata = $transactionContext->getMetadata();

    $traceId = $transactionContext->getTraceId() ?? $propagationContext->getTraceId();
    $transactionContext->setTraceId($traceId);
    $propagationContext->setTraceId($traceId);

    $propagationContext->setParentSpanId($transactionContext->getParentSpanId());
    $propagationContext->setSampleRand($metadata->getSampleRand());

    $dynamicSamplingContext = $metadata->getDynamicSamplingContext();
    if ($dynamicSamplingContext !== null) {
        $propagationContext->setDynamicSamplingContext($dynamicSamplingContext);
    }

    $hub = SentrySdk::getCurrentHub();
    $hub->configureScope(static function (Scope $scope) use ($propagationContext): void {
        $scope->setPropagationContext($propagationContext);
    });

    return $transactionContext;
}


function logger(): Logs
{
    return Logs::getInstance();
}


function metrics(): Metrics
{
    return Metrics::getInstance();
}

function traceMetrics(): TraceMetrics
{
    return TraceMetrics::getInstance();
}


function trace_metrics(): TraceMetrics
{
    return TraceMetrics::getInstance();
}


function addFeatureFlag(string $name, bool $result): void
{
    SentrySdk::getCurrentHub()->configureScope(static function (Scope $scope) use ($name, $result) {
        $scope->addFeatureFlag($name, $result);
    });
}


function flush(): void
{
    SentrySdk::flush();
}
