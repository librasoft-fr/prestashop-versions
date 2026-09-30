<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

use PsMcpServerDeps\Sentry\Integration\IntegrationInterface;
use PsMcpServerDeps\Sentry\Integration\IntegrationRegistry;
use PsMcpServerDeps\Sentry\Serializer\RepresentationSerializer;
use PsMcpServerDeps\Sentry\Serializer\RepresentationSerializerInterface;
use PsMcpServerDeps\Sentry\State\Scope;
use PsMcpServerDeps\Sentry\Transport\Result;
use PsMcpServerDeps\Sentry\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client implements ClientInterface
{
    public const PROTOCOL_VERSION = '7';

    public const SDK_IDENTIFIER = 'sentry.php';

    public const SDK_VERSION = '4.28.0';

    private const REGEX_PATTERN_DETECTION = '/^\/.*\/[imsu]*$/';

    private $options;

    private $transport;

    private $logger;

    private $integrations;

    private $stacktraceBuilder;

    private $sdkIdentifier;

    private $sdkVersion;

    public function __construct(
        Options $options,
        TransportInterface $transport,
        ?string $sdkIdentifier = null,
        ?string $sdkVersion = null,
        ?RepresentationSerializerInterface $representationSerializer = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->options = $options;
        $this->transport = $transport;
        $this->sdkIdentifier = $sdkIdentifier ?? self::SDK_IDENTIFIER;
        $this->sdkVersion = $sdkVersion ?? self::SDK_VERSION;
        $this->stacktraceBuilder = new StacktraceBuilder($options, $representationSerializer ?? new RepresentationSerializer($this->options));
        $this->logger = $logger ?? new NullLogger();

        $this->integrations = IntegrationRegistry::getInstance()->setupIntegrations($options, $this->logger);
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function getCspReportUrl(): ?string
    {
        $dsn = $this->options->getDsn();

        if ($dsn === null) {
            return null;
        }

        $endpoint = $dsn->getCspReportEndpointUrl();
        $query = array_filter([
            'sentry_release' => $this->options->getRelease(),
            'sentry_environment' => $this->options->getEnvironment(),
        ]);

        if (!empty($query)) {
            $endpoint .= '&' . http_build_query($query, '', '&');
        }

        return $endpoint;
    }

    public function captureMessage(string $message, ?Severity $level = null, ?Scope $scope = null, ?EventHint $hint = null): ?EventId
    {
        $event = Event::createEvent();
        $event->setMessage($message);
        $event->setLevel($level);

        return $this->captureEvent($event, $hint, $scope);
    }

    public function captureException(\Throwable $exception, ?Scope $scope = null, ?EventHint $hint = null): ?EventId
    {
        $className = \get_class($exception);
        if ($this->shouldIgnoreException($className)) {
            $this->logger->info(
                'The exception will be discarded because it matches an entry in "ignore_exceptions".',
                ['className' => $className]
            );

            return null;
        }

        $hint = $hint ?? new EventHint();

        if ($hint->exception === null) {
            $hint->exception = $exception;
        }

        return $this->captureEvent(Event::createEvent(), $hint, $scope);
    }

    public function captureEvent(Event $event, ?EventHint $hint = null, ?Scope $scope = null): ?EventId
    {
        if ($event->getType() !== EventType::clientReport()) {
            $event = $this->prepareEvent($event, $hint, $scope);
        }

        if ($event === null) {
            return null;
        }

        try {
            $result = $this->transport->send($event);
            $event = $result->getEvent();

            if ($event !== null) {
                return $event->getId();
            }
        } catch (\Throwable $exception) {
            $this->logger->error(
                \sprintf('Failed to send the event to Sentry. Reason: "%s".', $exception->getMessage()),
                ['exception' => $exception, 'event' => $event]
            );
        }

        return null;
    }

    public function captureLastError(?Scope $scope = null, ?EventHint $hint = null): ?EventId
    {
        $error = error_get_last();

        if ($error === null || !isset($error['message'][0])) {
            return null;
        }

        $exception = new \ErrorException(@$error['message'], 0, @$error['type'], @$error['file'], @$error['line']);

        return $this->captureException($exception, $scope, $hint);
    }

    public function getIntegration(string $className): ?IntegrationInterface
    {
        return $this->integrations[$className] ?? null;
    }

    public function flush(?int $timeout = null): Result
    {
        return $this->transport->close($timeout);
    }

    public function getStacktraceBuilder(): StacktraceBuilder
    {
        return $this->stacktraceBuilder;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    public function getSdkIdentifier(): string
    {
        return $this->sdkIdentifier;
    }

    public function getSdkVersion(): string
    {
        return $this->sdkVersion;
    }

    private function prepareEvent(Event $event, ?EventHint $hint = null, ?Scope $scope = null): ?Event
    {
        if ($hint !== null) {
            if ($hint->exception !== null && empty($event->getExceptions())) {
                $this->addThrowableToEvent($event, $hint->exception, $hint);
            }

            if ($hint->stacktrace !== null && $event->getStacktrace() === null) {
                $event->setStacktrace($hint->stacktrace);
            }
        }

        $this->addMissingStacktraceToEvent($event);

        $event->setSdkIdentifier($this->sdkIdentifier);
        $event->setSdkVersion($this->sdkVersion);

        $event->setTags(array_merge($this->options->getTags(), $event->getTags()));

        if ($event->getServerName() === null) {
            $event->setServerName($this->options->getServerName());
        }

        if ($event->getRelease() === null) {
            $event->setRelease($this->options->getRelease());
        }

        if ($event->getEnvironment() === null) {
            $event->setEnvironment($this->options->getEnvironment() ?? Event::DEFAULT_ENVIRONMENT);
        }

        $eventDescription = \sprintf(
            '%s%s [%s]',
            $event->getLevel() !== null ? $event->getLevel() . ' ' : '',
            (string) $event->getType(),
            (string) $event->getId()
        );

        $isEvent = EventType::event() === $event->getType();
        $sampleRate = $this->options->getSampleRate();

        if ($isEvent && $sampleRate < 1 && mt_rand(1, 100) / 100.0 > $sampleRate) {
            $this->logger->info(\sprintf('The %s will be discarded because it has been sampled.', $eventDescription), ['event' => $event]);

            return null;
        }

        $event = $this->applyIgnoreOptions($event, $eventDescription);

        if ($event === null) {
            return null;
        }

        if ($scope !== null) {
            $beforeEventProcessors = $event;
            $event = $scope->applyToEvent($event, $hint, $this->options);

            if ($event === null) {
                $this->logger->info(
                    \sprintf('The %s will be discarded because one of the event processors returned "null".', $eventDescription),
                    ['event' => $beforeEventProcessors]
                );

                return null;
            }
        }

        $beforeSendCallback = $event;
        $event = $this->applyBeforeSendCallback($event, $hint);

        if ($event === null) {
            $this->logger->info(
                \sprintf(
                    'The %s will be discarded because the "%s" callback returned "null".',
                    $eventDescription,
                    $this->getBeforeSendCallbackName($beforeSendCallback)
                ),
                ['event' => $beforeSendCallback]
            );
        }

        return $event;
    }

    private function shouldIgnoreException(string $className): bool
    {
        foreach ($this->options->getIgnoreExceptions() as $pattern) {
            if (preg_match(self::REGEX_PATTERN_DETECTION, $pattern)) {
                try {
                    if (preg_match($pattern, $className)) {
                        return true;
                    }
                } catch (\Throwable $e) {
                    $this->logger->warning(
                        \sprintf('Invalid regex pattern in ignore_exceptions: "%s". Error: %s', $pattern, $e->getMessage())
                    );
                    continue;
                }
            } else {
                if (is_a($className, $pattern, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function shouldIgnoreTransaction(string $transactionName): bool
    {
        foreach ($this->options->getIgnoreTransactions() as $pattern) {
            if (preg_match(self::REGEX_PATTERN_DETECTION, $pattern)) {
                try {
                    if (preg_match($pattern, $transactionName)) {
                        return true;
                    }
                } catch (\Throwable $e) {
                    $this->logger->warning(
                        \sprintf('Invalid regex pattern in ignore_transactions: "%s". Error: %s', $pattern, $e->getMessage())
                    );
                    continue;
                }
            } else {
                if ($transactionName === $pattern) {
                    return true;
                }
            }
        }

        return false;
    }

    private function applyIgnoreOptions(Event $event, string $eventDescription): ?Event
    {
        if ($event->getType() === EventType::event()) {
            $exceptions = $event->getExceptions();

            if (empty($exceptions)) {
                return $event;
            }

            foreach ($exceptions as $exception) {
                if ($this->shouldIgnoreException($exception->getType())) {
                    $this->logger->info(
                        \sprintf('The %s will be discarded because it matches an entry in "ignore_exceptions".', $eventDescription),
                        ['event' => $event]
                    );

                    return null;
                }
            }
        }

        if ($event->getType() === EventType::transaction()) {
            $transactionName = $event->getTransaction();

            if ($transactionName === null) {
                return $event;
            }

            if ($this->shouldIgnoreTransaction($transactionName)) {
                $this->logger->info(
                    \sprintf('The %s will be discarded because it matches a entry in "ignore_transactions".', $eventDescription),
                    ['event' => $event]
                );

                return null;
            }
        }

        return $event;
    }

    private function applyBeforeSendCallback(Event $event, ?EventHint $hint): ?Event
    {
        switch ($event->getType()) {
            case EventType::event():
                return ($this->options->getBeforeSendCallback())($event, $hint);
            case EventType::transaction():
                return ($this->options->getBeforeSendTransactionCallback())($event, $hint);
            case EventType::checkIn():
                return ($this->options->getBeforeSendCheckInCallback())($event, $hint);
            default:
                return $event;
        }
    }

    private function getBeforeSendCallbackName(Event $event): string
    {
        switch ($event->getType()) {
            case EventType::transaction():
                return 'before_send_transaction';
            case EventType::checkIn():
                return 'before_send_check_in';
            default:
                return 'before_send';
        }
    }

    private function addMissingStacktraceToEvent(Event $event): void
    {
        if (!$this->options->shouldAttachStacktrace()) {
            return;
        }

        if ($event->getStacktrace() !== null || !empty($event->getExceptions())) {
            return;
        }

        $event->setStacktrace($this->stacktraceBuilder->buildFromBacktrace(
            debug_backtrace(0),
            __FILE__,
            __LINE__ - 3
        ));
    }

    private function addThrowableToEvent(Event $event, \Throwable $exception, EventHint $hint): void
    {
        if ($exception instanceof \ErrorException && $event->getLevel() === null) {
            $event->setLevel(Severity::fromError($exception->getSeverity()));
        }

        $exceptions = [];

        do {
            $exceptions[] = new ExceptionDataBag(
                $exception,
                $this->stacktraceBuilder->buildFromException($exception),
                $hint->mechanism ?? new ExceptionMechanism(ExceptionMechanism::TYPE_GENERIC, true, ['code' => $exception->getCode()])
            );
        } while ($exception = $exception->getPrevious());

        $event->setExceptions($exceptions);
    }
}
