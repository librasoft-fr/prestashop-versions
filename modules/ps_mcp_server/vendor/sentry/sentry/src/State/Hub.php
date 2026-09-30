<?php

declare(strict_types=1);

namespace Sentry\State;

use Psr\Log\NullLogger;
use Sentry\Breadcrumb;
use Sentry\CheckIn;
use Sentry\CheckInStatus;
use Sentry\ClientInterface;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\EventId;
use Sentry\Integration\IntegrationInterface;
use Sentry\MonitorConfig;
use Sentry\Severity;
use Sentry\Tracing\SamplingContext;
use Sentry\Tracing\Span;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;


class Hub implements HubInterface
{
    
    private $stack = [];

    
    private $lastEventId;

    
    public function __construct(?ClientInterface $client = null, ?Scope $scope = null)
    {
        $this->stack[] = new Layer($client, $scope ?? new Scope());
    }

    
    public function getClient(): ?ClientInterface
    {
        return $this->getStackTop()->getClient();
    }

    
    public function getLastEventId(): ?EventId
    {
        return $this->lastEventId;
    }

    
    public function pushScope(): Scope
    {
        $clonedScope = clone $this->getScope();

        $this->stack[] = new Layer($this->getClient(), $clonedScope);

        return $clonedScope;
    }

    
    public function popScope(): bool
    {
        if (\count($this->stack) === 1) {
            return false;
        }

        return array_pop($this->stack) !== null;
    }

    
    public function withScope(callable $callback)
    {
        $scope = $this->pushScope();

        try {
            return $callback($scope);
        } finally {
            $this->popScope();
        }
    }

    
    public function configureScope(callable $callback): void
    {
        $callback($this->getScope());
    }

    
    public function bindClient(ClientInterface $client): void
    {
        $layer = $this->getStackTop();
        $layer->setClient($client);
    }

    
    public function captureMessage(string $message, ?Severity $level = null, ?EventHint $hint = null): ?EventId
    {
        $client = $this->getClient();

        if ($client !== null) {
            return $this->lastEventId = $client->captureMessage($message, $level, $this->getScope(), $hint);
        }

        return null;
    }

    
    public function captureException(\Throwable $exception, ?EventHint $hint = null): ?EventId
    {
        $client = $this->getClient();

        if ($client !== null) {
            return $this->lastEventId = $client->captureException($exception, $this->getScope(), $hint);
        }

        return null;
    }

    
    public function captureEvent(Event $event, ?EventHint $hint = null): ?EventId
    {
        $client = $this->getClient();

        if ($client !== null) {
            return $this->lastEventId = $client->captureEvent($event, $hint, $this->getScope());
        }

        return null;
    }

    
    public function captureLastError(?EventHint $hint = null): ?EventId
    {
        $client = $this->getClient();

        if ($client !== null) {
            return $this->lastEventId = $client->captureLastError($this->getScope(), $hint);
        }

        return null;
    }

    
    public function captureCheckIn(string $slug, CheckInStatus $status, $duration = null, ?MonitorConfig $monitorConfig = null, ?string $checkInId = null): ?string
    {
        $client = $this->getClient();

        if ($client === null) {
            return null;
        }

        $options = $client->getOptions();
        $event = Event::createCheckIn();
        $checkIn = new CheckIn(
            $slug,
            $status,
            $checkInId,
            $options->getRelease(),
            $options->getEnvironment(),
            $duration,
            $monitorConfig
        );
        $event->setCheckIn($checkIn);
        $this->captureEvent($event);

        return $checkIn->getId();
    }

    
    public function addBreadcrumb(Breadcrumb $breadcrumb): bool
    {
        $client = $this->getClient();

        if ($client === null) {
            return false;
        }

        $options = $client->getOptions();
        $beforeBreadcrumbCallback = $options->getBeforeBreadcrumbCallback();
        $maxBreadcrumbs = $options->getMaxBreadcrumbs();

        if ($maxBreadcrumbs <= 0) {
            return false;
        }

        $breadcrumb = $beforeBreadcrumbCallback($breadcrumb);

        if ($breadcrumb !== null) {
            $this->getScope()->addBreadcrumb($breadcrumb, $maxBreadcrumbs);
        }

        return $breadcrumb !== null;
    }

    
    public function getIntegration(string $className): ?IntegrationInterface
    {
        $client = $this->getClient();

        if ($client !== null) {
            return $client->getIntegration($className);
        }

        return null;
    }

    
    public function startTransaction(TransactionContext $context, array $customSamplingContext = []): Transaction
    {
        $transaction = new Transaction($context, $this);
        $client = $this->getClient();
        $options = $client !== null ? $client->getOptions() : null;
        $logger = $options !== null ? $options->getLoggerOrNullLogger() : new NullLogger();

        if ($options === null || !$options->isTracingEnabled()) {
            $transaction->setSampled(false);

            $logger->warning(\sprintf('Transaction [%s] was started but tracing is not enabled.', (string) $transaction->getTraceId()), ['context' => $context]);

            return $transaction;
        }

        $samplingContext = SamplingContext::getDefault($context);
        $samplingContext->setAdditionalContext($customSamplingContext);

        $sampleSource = 'context';
        $sampleRand = $context->getMetadata()->getSampleRand();

        if ($transaction->getSampled() === null) {
            $tracesSampler = $options->getTracesSampler();

            if ($tracesSampler !== null) {
                $sampleRate = $tracesSampler($samplingContext);
                $sampleSource = 'config:traces_sampler';
            } else {
                $parentSampleRate = $context->getMetadata()->getParentSamplingRate();
                if ($parentSampleRate !== null) {
                    $sampleRate = $parentSampleRate;
                    $sampleSource = 'parent:sample_rate';
                } else {
                    $sampleRate = $this->getSampleRate(
                        $samplingContext->getParentSampled(),
                        $options->getTracesSampleRate() ?? 0
                    );
                    $sampleSource = $samplingContext->getParentSampled() !== null ? 'parent:sampling_decision' : 'config:traces_sample_rate';
                }
            }

            if (!$this->isValidSampleRate($sampleRate)) {
                $transaction->setSampled(false);

                $logger->warning(\sprintf('Transaction [%s] was started but not sampled because sample rate (decided by %s) is invalid.', (string) $transaction->getTraceId(), $sampleSource), ['context' => $context]);

                return $transaction;
            }

            $transaction->getMetadata()->setSamplingRate($sampleRate);

            
            $dynamicSamplingContext = $context->getMetadata()->getDynamicSamplingContext();
            if ($dynamicSamplingContext !== null) {
                $dynamicSamplingContext->set('sample_rate', (string) $sampleRate, true);
            }

            if ($sampleRate === 0.0) {
                $transaction->setSampled(false);

                $logger->info(\sprintf('Transaction [%s] was started but not sampled because sample rate (decided by %s) is %s.', (string) $transaction->getTraceId(), $sampleSource, $sampleRate), ['context' => $context]);

                return $transaction;
            }

            $transaction->setSampled($sampleRand < $sampleRate);
        }

        if (!$transaction->getSampled()) {
            $logger->info(\sprintf('Transaction [%s] was started but not sampled, decided by %s.', (string) $transaction->getTraceId(), $sampleSource), ['context' => $context]);

            return $transaction;
        }

        $logger->info(\sprintf('Transaction [%s] was started and sampled, decided by %s.', (string) $transaction->getTraceId(), $sampleSource), ['context' => $context]);

        $transaction->initSpanRecorder();

        $profilesSampleSource = 'config:profiles_sample_rate';
        $profilesSampler = $options->getProfilesSampler();

        if ($profilesSampler !== null) {
            $profilesSampleRate = $profilesSampler($samplingContext);
            $profilesSampleSource = 'config:profiles_sampler';
        } else {
            $profilesSampleRate = $options->getProfilesSampleRate();
        }

        if ($profilesSampleRate === null) {
            $logger->info(\sprintf('Transaction [%s] is not profiling because neither `profiles_sample_rate` nor `profiles_sampler` option is set.', (string) $transaction->getTraceId()));
        } elseif (!$this->isValidSampleRate($profilesSampleRate)) {
            $logger->warning(\sprintf('Transaction [%s] is not profiling because profile sample rate (decided by %s) is invalid.', (string) $transaction->getTraceId(), $profilesSampleSource));
        } elseif ($this->sample($profilesSampleRate)) {
            $logger->info(\sprintf('Transaction [%s] started profiling because it was sampled.', (string) $transaction->getTraceId()));

            $transaction->initProfiler()->start();
        } else {
            $logger->info(\sprintf('Transaction [%s] is not profiling because it was not sampled.', (string) $transaction->getTraceId()));
        }

        return $transaction;
    }

    
    public function getTransaction(): ?Transaction
    {
        return $this->getScope()->getTransaction();
    }

    
    public function setSpan(?Span $span): HubInterface
    {
        $this->getScope()->setSpan($span);

        return $this;
    }

    
    public function getSpan(): ?Span
    {
        return $this->getScope()->getSpan();
    }

    
    private function getScope(): Scope
    {
        return $this->getStackTop()->getScope();
    }

    
    private function getStackTop(): Layer
    {
        return $this->stack[\count($this->stack) - 1];
    }

    private function getSampleRate(?bool $hasParentBeenSampled, float $fallbackSampleRate): float
    {
        if ($hasParentBeenSampled === true) {
            return 1.0;
        }

        if ($hasParentBeenSampled === false) {
            return 0.0;
        }

        return $fallbackSampleRate;
    }

    
    private function sample($sampleRate): bool
    {
        if ($sampleRate === 0.0 || $sampleRate === null) {
            return false;
        }

        if ($sampleRate === 1.0) {
            return true;
        }

        return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax() < $sampleRate;
    }

    
    private function isValidSampleRate($sampleRate): bool
    {
        if (!\is_float($sampleRate) && !\is_int($sampleRate)) {
            return false;
        }

        if ($sampleRate < 0 || $sampleRate > 1) {
            return false;
        }

        return true;
    }
}
