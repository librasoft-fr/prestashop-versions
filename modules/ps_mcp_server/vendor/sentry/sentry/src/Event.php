<?php

declare(strict_types=1);

namespace Sentry;

use Sentry\ClientReport\DiscardedEvent;
use Sentry\Context\OsContext;
use Sentry\Context\RuntimeContext;
use Sentry\Logs\Log;
use Sentry\Metrics\Types\Metric;
use Sentry\Profiling\Profile;
use Sentry\Tracing\Span;


final class Event
{
    public const DEFAULT_ENVIRONMENT = 'production';

    
    private $id;

    
    private $timestamp;

    
    private $startTimestamp;

    
    private $level;

    
    private $logger;

    
    private $transaction;

    
    private $checkIn;

    
    private $logs = [];

    
    private $metrics = [];

    
    private $serverName;

    
    private $release;

    
    private $message;

    
    private $messageFormatted;

    
    private $messageParams = [];

    
    private $environment;

    
    private $modules = [];

    
    private $request = [];

    
    private $tags = [];

    
    private $osContext;

    
    private $runtimeContext;

    
    private $user;

    
    private $contexts = [];

    
    private $extra = [];

    
    private $fingerprint = [];

    
    private $breadcrumbs = [];

    
    private $spans = [];

    
    private $exceptions = [];

    
    private $stacktrace;

    
    private $sdkMetadata = [];

    
    private $sdkIdentifier = Client::SDK_IDENTIFIER;

    
    private $sdkVersion = Client::SDK_VERSION;

    
    private $sdkPackages = [
        [
            'name' => 'composer:sentry/sentry',
            'version' => Client::SDK_VERSION,
        ],
    ];

    
    private $type;

    
    private $profile;

    
    private $clientReports = [];

    private function __construct(?EventId $eventId, EventType $eventType)
    {
        $this->id = $eventId ?? EventId::generate();
        $this->timestamp = microtime(true);
        $this->type = $eventType;
    }

    
    public static function createEvent(?EventId $eventId = null): self
    {
        return new self($eventId, EventType::event());
    }

    
    public static function createTransaction(?EventId $eventId = null): self
    {
        return new self($eventId, EventType::transaction());
    }

    public static function createCheckIn(?EventId $eventId = null): self
    {
        return new self($eventId, EventType::checkIn());
    }

    public static function createLogs(?EventId $eventId = null): self
    {
        return new self($eventId, EventType::logs());
    }

    public static function createMetrics(?EventId $eventId = null): self
    {
        return new self($eventId, EventType::metrics());
    }

    public static function createClientReport(?EventId $eventId = null): self
    {
        return new self($eventId, EventType::clientReport());
    }

    
    public function getId(): EventId
    {
        return $this->id;
    }

    
    public function getSdkIdentifier(): string
    {
        return $this->sdkIdentifier;
    }

    
    public function setSdkIdentifier(string $sdkIdentifier): self
    {
        $this->sdkIdentifier = $sdkIdentifier;

        return $this;
    }

    
    public function getSdkVersion(): string
    {
        return $this->sdkVersion;
    }

    
    public function setSdkVersion(string $sdkVersion): self
    {
        $this->sdkVersion = $sdkVersion;

        return $this;
    }

    
    public function appendSdkPackage(array $package): self
    {
        $this->sdkPackages[] = $package;

        return $this;
    }

    
    public function getSdkPayload(): array
    {
        return [
            'name' => $this->sdkIdentifier,
            'version' => $this->sdkVersion,
            'packages' => $this->sdkPackages,
        ];
    }

    
    public function getTimestamp(): ?float
    {
        return $this->timestamp;
    }

    
    public function setTimestamp(?float $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    
    public function getLevel(): ?Severity
    {
        return $this->level;
    }

    
    public function setLevel(?Severity $level): self
    {
        $this->level = $level;

        return $this;
    }

    
    public function getLogger(): ?string
    {
        return $this->logger;
    }

    
    public function setLogger(?string $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    
    public function getTransaction(): ?string
    {
        return $this->transaction;
    }

    
    public function setTransaction(?string $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getCheckIn(): ?CheckIn
    {
        return $this->checkIn;
    }

    public function setCheckIn(?CheckIn $checkIn): self
    {
        $this->checkIn = $checkIn;

        return $this;
    }

    
    public function getLogs(): array
    {
        return $this->logs;
    }

    
    public function setLogs(array $logs): self
    {
        $this->logs = $logs;

        return $this;
    }

    
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    
    public function setMetrics(array $metrics): self
    {
        $this->metrics = $metrics;

        return $this;
    }

    
    public function getMetricsSummary(): array
    {
        return [];
    }

    
    public function setMetricsSummary(array $metricsSummary): self
    {
        return $this;
    }

    
    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    
    public function setServerName(?string $serverName): self
    {
        $this->serverName = $serverName;

        return $this;
    }

    
    public function getRelease(): ?string
    {
        return $this->release;
    }

    
    public function setRelease(?string $release): self
    {
        $this->release = $release;

        return $this;
    }

    
    public function getMessage(): ?string
    {
        return $this->message;
    }

    
    public function getMessageFormatted(): ?string
    {
        return $this->messageFormatted;
    }

    
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }

    
    public function setMessage(string $message, array $params = [], ?string $formatted = null): self
    {
        $this->message = $message;
        $this->messageParams = $params;
        $this->messageFormatted = $formatted;

        return $this;
    }

    
    public function getModules(): array
    {
        return $this->modules;
    }

    
    public function setModules(array $modules): self
    {
        $this->modules = $modules;

        return $this;
    }

    
    public function getRequest(): array
    {
        return $this->request;
    }

    
    public function setRequest(array $request): self
    {
        $this->request = $request;

        return $this;
    }

    
    public function getContexts(): array
    {
        return $this->contexts;
    }

    
    public function setContext(string $name, array $data): self
    {
        if (!empty($data)) {
            $this->contexts[$name] = $data;
        }

        return $this;
    }

    
    public function getExtra(): array
    {
        return $this->extra;
    }

    
    public function setExtra(array $extra): self
    {
        $this->extra = $extra;

        return $this;
    }

    
    public function getTags(): array
    {
        return $this->tags;
    }

    
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    
    public function setTag(string $key, string $value): self
    {
        $this->tags[$key] = $value;

        return $this;
    }

    
    public function removeTag(string $key): self
    {
        unset($this->tags[$key]);

        return $this;
    }

    
    public function getUser(): ?UserDataBag
    {
        return $this->user;
    }

    
    public function setUser(?UserDataBag $user): self
    {
        $this->user = $user;

        return $this;
    }

    
    public function getOsContext(): ?OsContext
    {
        return $this->osContext;
    }

    
    public function setOsContext(?OsContext $osContext): self
    {
        $this->osContext = $osContext;

        return $this;
    }

    
    public function getRuntimeContext(): ?RuntimeContext
    {
        return $this->runtimeContext;
    }

    
    public function setRuntimeContext(?RuntimeContext $runtimeContext): self
    {
        $this->runtimeContext = $runtimeContext;

        return $this;
    }

    
    public function getFingerprint(): array
    {
        return $this->fingerprint;
    }

    
    public function setFingerprint(array $fingerprint): self
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    
    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    
    public function setEnvironment(?string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    
    public function setBreadcrumb(array $breadcrumbs): self
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    
    public function setExceptions(array $exceptions): self
    {
        foreach ($exceptions as $exception) {
            if (!$exception instanceof ExceptionDataBag) {
                throw new \UnexpectedValueException(\sprintf('Expected an instance of the "%s" class. Got: "%s".', ExceptionDataBag::class, get_debug_type($exception)));
            }
        }

        $this->exceptions = $exceptions;

        return $this;
    }

    
    public function getStacktrace(): ?Stacktrace
    {
        return $this->stacktrace;
    }

    
    public function setStacktrace(?Stacktrace $stacktrace): self
    {
        $this->stacktrace = $stacktrace;

        return $this;
    }

    public function getType(): EventType
    {
        return $this->type;
    }

    
    public function setSdkMetadata(string $name, $data): self
    {
        $this->sdkMetadata[$name] = $data;

        return $this;
    }

    
    public function getSdkMetadata(?string $name = null)
    {
        if ($name !== null) {
            return $this->sdkMetadata[$name] ?? null;
        }

        return $this->sdkMetadata;
    }

    
    public function getStartTimestamp(): ?float
    {
        return $this->startTimestamp;
    }

    
    public function setStartTimestamp(?float $startTimestamp): self
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    
    public function getSpans(): array
    {
        return $this->spans;
    }

    
    public function setSpans(array $spans): self
    {
        $this->spans = $spans;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getTraceId(): ?string
    {
        $traceId = $this->getContexts()['trace']['trace_id'];

        if (\is_string($traceId) && !empty($traceId)) {
            return $traceId;
        }

        return null;
    }

    
    public function setClientReports(array $clientReports): self
    {
        $this->clientReports = $clientReports;

        return $this;
    }

    
    public function getClientReports(): array
    {
        return $this->clientReports;
    }
}
