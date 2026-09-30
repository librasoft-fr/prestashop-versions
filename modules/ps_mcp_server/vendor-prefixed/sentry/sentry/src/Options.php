<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

use PsMcpServerDeps\Sentry\HttpClient\HttpClientInterface;
use PsMcpServerDeps\Sentry\Logs\Log;
use PsMcpServerDeps\Sentry\Metrics\Types\Metric;
use PsMcpServerDeps\Sentry\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\Options as SymfonyOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Options
{
    public const DEFAULT_MAX_BREADCRUMBS = 100;

    public const DEFAULT_HTTP_TIMEOUT = 5;

    public const DEFAULT_HTTP_CONNECT_TIMEOUT = 2;

    private $options;

    private $resolver;

    public function __construct(array $options = [])
    {
        $this->resolver = new OptionsResolver();

        $this->configureOptions($this->resolver);

        if (isset($options['strict_trace_propagation']) && !isset($options['strict_trace_continuation'])) {
            $options['strict_trace_continuation'] = $options['strict_trace_propagation'];
        }

        $this->options = $this->resolver->resolve($options);

        if ($this->options['enable_tracing'] === true && $this->options['traces_sample_rate'] === null) {
            $this->options = array_merge($this->options, ['traces_sample_rate' => 1]);
        }
    }

    public function getPrefixes(): array
    {
        return $this->options['prefixes'];
    }

    public function setPrefixes(array $prefixes): self
    {
        $options = array_merge($this->options, ['prefixes' => $prefixes]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getSampleRate(): float
    {
        return $this->options['sample_rate'];
    }

    public function setSampleRate(float $sampleRate): self
    {
        $options = array_merge($this->options, ['sample_rate' => $sampleRate]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getTracesSampleRate(): ?float
    {
        return $this->options['traces_sample_rate'];
    }

    public function setEnableTracing(?bool $enableTracing): self
    {
        $options = array_merge($this->options, ['enable_tracing' => $enableTracing]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getEnableTracing(): ?bool
    {
        return $this->options['enable_tracing'];
    }

    public function setEnableLogs(?bool $enableLogs): self
    {
        $options = array_merge($this->options, ['enable_logs' => $enableLogs]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getEnableLogs(): bool
    {
        return $this->options['enable_logs'] ?? false;
    }

    public function getLogFlushThreshold(): ?int
    {
        $logFlushThreshold = $this->options['log_flush_threshold'];

        return $logFlushThreshold;
    }

    public function setLogFlushThreshold(?int $logFlushThreshold): self
    {
        $options = array_merge($this->options, ['log_flush_threshold' => $logFlushThreshold]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getMetricFlushThreshold(): ?int
    {
        $metricFlushThreshold = $this->options['metric_flush_threshold'];

        return $metricFlushThreshold;
    }

    public function setMetricFlushThreshold(?int $metricFlushThreshold): self
    {
        $options = array_merge($this->options, ['metric_flush_threshold' => $metricFlushThreshold]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function setEnableMetrics(bool $enableTracing): self
    {
        $options = array_merge($this->options, ['enable_metrics' => $enableTracing]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getEnableMetrics(): bool
    {
        $enableMetrics = $this->options['enable_metrics'] ?? true;

        return $enableMetrics;
    }

    public function setTracesSampleRate(?float $sampleRate): self
    {
        $options = array_merge($this->options, ['traces_sample_rate' => $sampleRate]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getProfilesSampleRate(): ?float
    {
        $value = $this->options['profiles_sample_rate'] ?? null;

        return $value ?? null;
    }

    public function setProfilesSampleRate(?float $sampleRate): self
    {
        $options = array_merge($this->options, ['profiles_sample_rate' => $sampleRate]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getProfilesSampler(): ?callable
    {
        $value = $this->options['profiles_sampler'];

        return $value;
    }

    public function setProfilesSampler(?callable $sampler): self
    {
        $options = array_merge($this->options, ['profiles_sampler' => $sampler]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function isTracingEnabled(): bool
    {
        if ($this->getEnableTracing() !== null && $this->getEnableTracing() === false) {
            return false;
        }

        return $this->getTracesSampleRate() !== null || $this->getTracesSampler() !== null;
    }

    public function shouldAttachStacktrace(): bool
    {
        return $this->options['attach_stacktrace'];
    }

    public function setAttachStacktrace(bool $enable): self
    {
        $options = array_merge($this->options, ['attach_stacktrace' => $enable]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function shouldAttachMetricCodeLocations(): bool
    {
        return $this->options['attach_metric_code_locations'];
    }

    public function setAttachMetricCodeLocations(bool $enable): self
    {
        $options = array_merge($this->options, ['attach_metric_code_locations' => $enable]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getContextLines(): ?int
    {
        return $this->options['context_lines'];
    }

    public function setContextLines(?int $contextLines): self
    {
        $options = array_merge($this->options, ['context_lines' => $contextLines]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getEnvironment(): ?string
    {
        return $this->options['environment'];
    }

    public function setEnvironment(?string $environment): self
    {
        $options = array_merge($this->options, ['environment' => $environment]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getInAppExcludedPaths(): array
    {
        return $this->options['in_app_exclude'];
    }

    public function setInAppExcludedPaths(array $paths): self
    {
        $options = array_merge($this->options, ['in_app_exclude' => $paths]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getInAppIncludedPaths(): array
    {
        return $this->options['in_app_include'];
    }

    public function setInAppIncludedPaths(array $paths): self
    {
        $options = array_merge($this->options, ['in_app_include' => $paths]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->options['logger'];
    }

    public function getLoggerOrNullLogger(): LoggerInterface
    {
        return $this->getLogger() ?? new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $options = array_merge($this->options, ['logger' => $logger]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function isSpotlightEnabled(): bool
    {
        return \is_string($this->options['spotlight']) || $this->options['spotlight'];
    }

    public function enableSpotlight($enable): self
    {
        $options = array_merge($this->options, ['spotlight' => $enable]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getSpotlightUrl(): string
    {
        if (\is_string($this->options['spotlight'])) {
            return $this->options['spotlight'];
        }

        return $this->options['spotlight_url'];
    }

    public function setSpotlightUrl(string $url): self
    {
        $options = array_merge($this->options, ['spotlight_url' => $url]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getRelease(): ?string
    {
        return $this->options['release'];
    }

    public function setRelease(?string $release): self
    {
        $options = array_merge($this->options, ['release' => $release]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getDsn(): ?Dsn
    {
        return $this->options['dsn'];
    }

    public function getOrgId(): ?int
    {
        return $this->options['org_id'];
    }

    public function setOrgId(int $orgId): self
    {
        $options = array_merge($this->options, ['org_id' => $orgId]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getServerName(): string
    {
        return $this->options['server_name'];
    }

    public function setServerName(string $serverName): self
    {
        $options = array_merge($this->options, ['server_name' => $serverName]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getIgnoreExceptions(): array
    {
        return $this->options['ignore_exceptions'];
    }

    public function setIgnoreExceptions(array $ignoreErrors): self
    {
        $options = array_merge($this->options, ['ignore_exceptions' => $ignoreErrors]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getIgnoreTransactions(): array
    {
        return $this->options['ignore_transactions'];
    }

    public function setIgnoreTransactions(array $ignoreTransaction): self
    {
        $options = array_merge($this->options, ['ignore_transactions' => $ignoreTransaction]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getBeforeSendCallback(): callable
    {
        return $this->options['before_send'];
    }

    public function setBeforeSendCallback(callable $callback): self
    {
        $options = array_merge($this->options, ['before_send' => $callback]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getBeforeSendTransactionCallback(): callable
    {
        return $this->options['before_send_transaction'];
    }

    public function setBeforeSendTransactionCallback(callable $callback): self
    {
        $options = array_merge($this->options, ['before_send_transaction' => $callback]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getBeforeSendCheckInCallback(): callable
    {
        return $this->options['before_send_check_in'];
    }

    public function setBeforeSendCheckInCallback(callable $callback): self
    {
        $options = array_merge($this->options, ['before_send_check_in' => $callback]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getBeforeSendLogCallback(): callable
    {
        return $this->options['before_send_log'];
    }

    public function setBeforeSendLogCallback(callable $callback): self
    {
        $options = array_merge($this->options, ['before_send_log' => $callback]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getBeforeSendMetricsCallback(): callable
    {
        return $this->options['before_send_metrics'];
    }

    public function getBeforeSendMetricCallback(): callable
    {
        $callback = $this->options['before_send_metric'];

        return $callback;
    }

    public function setBeforeSendMetricCallback(callable $callback): self
    {
        $options = array_merge($this->options, ['before_send_metric' => $callback]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function setBeforeSendMetricsCallback(callable $callback): self
    {
        $options = array_merge($this->options, ['before_send_metrics' => $callback]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getTracePropagationTargets(): ?array
    {
        return $this->options['trace_propagation_targets'];
    }

    public function setTracePropagationTargets(array $tracePropagationTargets): self
    {
        $options = array_merge($this->options, ['trace_propagation_targets' => $tracePropagationTargets]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function isStrictTraceContinuationEnabled(): bool
    {
        $result = $this->options['strict_trace_continuation'];

        return $result;
    }

    public function enableStrictTraceContinuation(bool $strictTraceContinuation): self
    {
        $options = array_merge($this->options, ['strict_trace_continuation' => $strictTraceContinuation]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function isStrictTracePropagationEnabled(): bool
    {
        return $this->isStrictTraceContinuationEnabled();
    }

    public function enableStrictTracePropagation(bool $strictTracePropagation): self
    {
        return $this->enableStrictTraceContinuation($strictTracePropagation);
    }

    public function getTags(): array
    {
        return $this->options['tags'];
    }

    public function setTags(array $tags): self
    {
        $options = array_merge($this->options, ['tags' => $tags]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getErrorTypes(): int
    {
        return $this->options['error_types'] ?? error_reporting();
    }

    public function setErrorTypes(int $errorTypes): self
    {
        $options = array_merge($this->options, ['error_types' => $errorTypes]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getMaxBreadcrumbs(): int
    {
        return $this->options['max_breadcrumbs'];
    }

    public function setMaxBreadcrumbs(int $maxBreadcrumbs): self
    {
        $options = array_merge($this->options, ['max_breadcrumbs' => $maxBreadcrumbs]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getBeforeBreadcrumbCallback(): callable
    {
        return $this->options['before_breadcrumb'];
    }

    public function setBeforeBreadcrumbCallback(callable $callback): self
    {
        $options = array_merge($this->options, ['before_breadcrumb' => $callback]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function setIntegrations($integrations): self
    {
        $options = array_merge($this->options, ['integrations' => $integrations]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getIntegrations()
    {
        return $this->options['integrations'];
    }

    public function setTransport(TransportInterface $transport): self
    {
        $options = array_merge($this->options, ['transport' => $transport]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getTransport(): ?TransportInterface
    {
        return $this->options['transport'];
    }

    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $options = array_merge($this->options, ['http_client' => $httpClient]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getHttpClient(): ?HttpClientInterface
    {
        return $this->options['http_client'];
    }

    public function shouldSendDefaultPii(): bool
    {
        return $this->options['send_default_pii'];
    }

    public function setSendDefaultPii(bool $enable): self
    {
        $options = array_merge($this->options, ['send_default_pii' => $enable]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function hasDefaultIntegrations(): bool
    {
        return $this->options['default_integrations'];
    }

    public function setDefaultIntegrations(bool $enable): self
    {
        $options = array_merge($this->options, ['default_integrations' => $enable]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getMaxValueLength(): int
    {
        return $this->options['max_value_length'];
    }

    public function setMaxValueLength(int $maxValueLength): self
    {
        $options = array_merge($this->options, ['max_value_length' => $maxValueLength]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getHttpProxy(): ?string
    {
        return $this->options['http_proxy'];
    }

    public function setHttpProxy(?string $httpProxy): self
    {
        $options = array_merge($this->options, ['http_proxy' => $httpProxy]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getHttpProxyAuthentication(): ?string
    {
        return $this->options['http_proxy_authentication'];
    }

    public function setHttpProxyAuthentication(?string $httpProxy): self
    {
        $options = array_merge($this->options, ['http_proxy_authentication' => $httpProxy]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getHttpConnectTimeout(): float
    {
        return $this->options['http_connect_timeout'];
    }

    public function setHttpConnectTimeout(float $httpConnectTimeout): self
    {
        $options = array_merge($this->options, ['http_connect_timeout' => $httpConnectTimeout]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getHttpTimeout(): float
    {
        return $this->options['http_timeout'];
    }

    public function setHttpTimeout(float $httpTimeout): self
    {
        $options = array_merge($this->options, ['http_timeout' => $httpTimeout]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getHttpSslVerifyPeer(): bool
    {
        return $this->options['http_ssl_verify_peer'];
    }

    public function setHttpSslVerifyPeer(bool $httpSslVerifyPeer): self
    {
        $options = array_merge($this->options, ['http_ssl_verify_peer' => $httpSslVerifyPeer]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getHttpSslNativeCa(): bool
    {
        return $this->options['http_ssl_native_ca'];
    }

    public function setHttpSslNativeCa(bool $httpSslNativeCa): self
    {
        $options = array_merge($this->options, ['http_ssl_native_ca' => $httpSslNativeCa]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function isHttpCompressionEnabled(): bool
    {
        return $this->options['http_compression'];
    }

    public function setEnableHttpCompression(bool $enabled): self
    {
        $options = array_merge($this->options, ['http_compression' => $enabled]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function isShareHandleEnabled(): bool
    {
        $shareHandleEnabled = $this->options['http_enable_curl_share_handle'];

        return $shareHandleEnabled;
    }

    public function setEnableShareHandle(bool $enabled): self
    {
        $options = array_merge($this->options, ['http_enable_curl_share_handle' => $enabled]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function shouldCaptureSilencedErrors(): bool
    {
        return $this->options['capture_silenced_errors'];
    }

    public function setCaptureSilencedErrors(bool $shouldCapture): self
    {
        $options = array_merge($this->options, ['capture_silenced_errors' => $shouldCapture]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getMaxRequestBodySize(): string
    {
        return $this->options['max_request_body_size'];
    }

    public function setMaxRequestBodySize(string $maxRequestBodySize): self
    {
        $options = array_merge($this->options, ['max_request_body_size' => $maxRequestBodySize]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getClassSerializers(): array
    {
        return $this->options['class_serializers'];
    }

    public function setClassSerializers(array $serializers): self
    {
        $options = array_merge($this->options, ['class_serializers' => $serializers]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    public function getTracesSampler(): ?callable
    {
        return $this->options['traces_sampler'];
    }

    public function setTracesSampler(?callable $sampler): self
    {
        $options = array_merge($this->options, ['traces_sampler' => $sampler]);

        $this->options = $this->resolver->resolve($options);

        return $this;
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'integrations' => [],
            'default_integrations' => true,
            'prefixes' => array_filter(explode(\PATH_SEPARATOR, get_include_path() ?: '')),
            'sample_rate' => 1,
            'enable_tracing' => null,
            'enable_logs' => false,
            'log_flush_threshold' => null,
            'enable_metrics' => true,
            'metric_flush_threshold' => null,
            'traces_sample_rate' => null,
            'traces_sampler' => null,
            'profiles_sample_rate' => null,
            'profiles_sampler' => null,
            'attach_stacktrace' => false,

            'attach_metric_code_locations' => false,
            'context_lines' => 5,
            'environment' => $_SERVER['SENTRY_ENVIRONMENT'] ?? null,
            'logger' => null,
            'spotlight' => $_SERVER['SENTRY_SPOTLIGHT'] ?? null,

            'spotlight_url' => 'http://localhost:8969',
            'release' => $_SERVER['SENTRY_RELEASE'] ?? $_SERVER['AWS_LAMBDA_FUNCTION_VERSION'] ?? null,
            'dsn' => $_SERVER['SENTRY_DSN'] ?? null,
            'org_id' => null,
            'server_name' => gethostname(),
            'ignore_exceptions' => [],
            'ignore_transactions' => [],
            'before_send' => static function (Event $event): Event {
                return $event;
            },
            'before_send_transaction' => static function (Event $transaction): Event {
                return $transaction;
            },
            'before_send_check_in' => static function (Event $checkIn): Event {
                return $checkIn;
            },
            'before_send_log' => static function (Log $log): Log {
                return $log;
            },

            'before_send_metrics' => static function (Event $metrics): ?Event {
                return null;
            },
            'before_send_metric' => static function (Metric $metric): Metric {
                return $metric;
            },
            'trace_propagation_targets' => null,
            'strict_trace_continuation' => false,
            'strict_trace_propagation' => false,
            'tags' => [],
            'error_types' => null,
            'max_breadcrumbs' => self::DEFAULT_MAX_BREADCRUMBS,
            'before_breadcrumb' => static function (Breadcrumb $breadcrumb): Breadcrumb {
                return $breadcrumb;
            },
            'in_app_exclude' => [],
            'in_app_include' => [],
            'send_default_pii' => false,
            'max_value_length' => 1024,
            'transport' => null,
            'http_client' => null,
            'http_proxy' => null,
            'http_proxy_authentication' => null,
            'http_connect_timeout' => self::DEFAULT_HTTP_CONNECT_TIMEOUT,
            'http_timeout' => self::DEFAULT_HTTP_TIMEOUT,
            'http_ssl_verify_peer' => true,
            'http_ssl_native_ca' => false,
            'http_compression' => true,
            'http_enable_curl_share_handle' => true,
            'capture_silenced_errors' => false,
            'max_request_body_size' => 'medium',
            'class_serializers' => [],
        ]);

        $resolver->setAllowedTypes('prefixes', 'string[]');
        $resolver->setAllowedTypes('sample_rate', ['int', 'float']);
        $resolver->setAllowedTypes('enable_tracing', ['null', 'bool']);
        $resolver->setAllowedTypes('enable_logs', 'bool');
        $resolver->setAllowedTypes('log_flush_threshold', ['null', 'int']);
        $resolver->setAllowedTypes('enable_metrics', 'bool');
        $resolver->setAllowedTypes('metric_flush_threshold', ['null', 'int']);
        $resolver->setAllowedTypes('traces_sample_rate', ['null', 'int', 'float']);
        $resolver->setAllowedTypes('traces_sampler', ['null', 'callable']);
        $resolver->setAllowedTypes('profiles_sample_rate', ['null', 'int', 'float']);
        $resolver->setAllowedTypes('profiles_sampler', ['null', 'callable']);
        $resolver->setAllowedTypes('attach_stacktrace', 'bool');
        $resolver->setAllowedTypes('attach_metric_code_locations', 'bool');
        $resolver->setAllowedTypes('context_lines', ['null', 'int']);
        $resolver->setAllowedTypes('environment', ['null', 'string']);
        $resolver->setAllowedTypes('in_app_exclude', 'string[]');
        $resolver->setAllowedTypes('in_app_include', 'string[]');
        $resolver->setAllowedTypes('logger', ['null', LoggerInterface::class]);
        $resolver->setAllowedTypes('spotlight', ['bool', 'string', 'null']);
        $resolver->setAllowedTypes('spotlight_url', 'string');
        $resolver->setAllowedTypes('release', ['null', 'string']);
        $resolver->setAllowedTypes('dsn', ['null', 'string', 'bool', Dsn::class]);
        $resolver->setAllowedTypes('org_id', ['null', 'int']);
        $resolver->setAllowedTypes('server_name', 'string');
        $resolver->setAllowedTypes('before_send', ['callable']);
        $resolver->setAllowedTypes('before_send_transaction', ['callable']);
        $resolver->setAllowedTypes('before_send_log', 'callable');
        $resolver->setAllowedTypes('before_send_metric', ['callable']);
        $resolver->setAllowedTypes('ignore_exceptions', 'string[]');
        $resolver->setAllowedTypes('ignore_transactions', 'string[]');
        $resolver->setAllowedTypes('trace_propagation_targets', ['null', 'string[]']);
        $resolver->setAllowedTypes('strict_trace_continuation', 'bool');
        $resolver->setAllowedTypes('strict_trace_propagation', 'bool');
        $resolver->setAllowedTypes('tags', 'string[]');
        $resolver->setAllowedTypes('error_types', ['null', 'int']);
        $resolver->setAllowedTypes('max_breadcrumbs', 'int');
        $resolver->setAllowedTypes('before_breadcrumb', ['callable']);
        $resolver->setAllowedTypes('integrations', ['PsMcpServerDeps\\Sentry\\Integration\\IntegrationInterface[]', 'callable']);
        $resolver->setAllowedTypes('send_default_pii', 'bool');
        $resolver->setAllowedTypes('default_integrations', 'bool');
        $resolver->setAllowedTypes('max_value_length', 'int');
        $resolver->setAllowedTypes('transport', ['null', TransportInterface::class]);
        $resolver->setAllowedTypes('http_client', ['null', HttpClientInterface::class]);
        $resolver->setAllowedTypes('http_proxy', ['null', 'string']);
        $resolver->setAllowedTypes('http_proxy_authentication', ['null', 'string']);
        $resolver->setAllowedTypes('http_connect_timeout', ['int', 'float']);
        $resolver->setAllowedTypes('http_timeout', ['int', 'float']);
        $resolver->setAllowedTypes('http_ssl_verify_peer', 'bool');
        $resolver->setAllowedTypes('http_ssl_native_ca', 'bool');
        $resolver->setAllowedTypes('http_compression', 'bool');
        $resolver->setAllowedTypes('http_enable_curl_share_handle', 'bool');
        $resolver->setAllowedTypes('capture_silenced_errors', 'bool');
        $resolver->setAllowedTypes('max_request_body_size', 'string');
        $resolver->setAllowedTypes('class_serializers', 'array');

        $resolver->setAllowedValues('max_request_body_size', ['none', 'never', 'small', 'medium', 'always']);
        $resolver->setAllowedValues('dsn', \Closure::fromCallable([$this, 'validateDsnOption']));
        $resolver->setAllowedValues('max_breadcrumbs', \Closure::fromCallable([$this, 'validateMaxBreadcrumbsOptions']));
        $resolver->setAllowedValues('class_serializers', \Closure::fromCallable([$this, 'validateClassSerializersOption']));
        $resolver->setAllowedValues('context_lines', \Closure::fromCallable([$this, 'validateContextLinesOption']));
        $resolver->setAllowedValues('log_flush_threshold', \Closure::fromCallable([$this, 'validateLogFlushThresholdOption']));
        $resolver->setAllowedValues('metric_flush_threshold', \Closure::fromCallable([$this, 'validateMetricFlushThresholdOption']));

        $resolver->setNormalizer('dsn', \Closure::fromCallable([$this, 'normalizeDsnOption']));

        $resolver->setNormalizer('prefixes', function (SymfonyOptions $options, array $value) {
            return array_map([$this, 'normalizeAbsolutePath'], $value);
        });

        $resolver->setNormalizer('spotlight_url', \Closure::fromCallable([$this, 'normalizeSpotlightUrl']));
        $resolver->setNormalizer('spotlight', \Closure::fromCallable([$this, 'normalizeBooleanOrUrl']));

        $resolver->setNormalizer('in_app_exclude', function (SymfonyOptions $options, array $value) {
            return array_map([$this, 'normalizeAbsolutePath'], $value);
        });

        $resolver->setNormalizer('in_app_include', function (SymfonyOptions $options, array $value) {
            return array_map([$this, 'normalizeAbsolutePath'], $value);
        });
    }

    private function normalizeAbsolutePath(string $value): string
    {
        $path = @realpath($value);

        if ($path === false) {
            $path = $value;
        }

        return $path;
    }

    private function normalizeBooleanOrUrl(SymfonyOptions $options, ?string $booleanOrUrl)
    {
        if (empty($booleanOrUrl)) {
            return false;
        }

        if (filter_var($booleanOrUrl, \FILTER_VALIDATE_URL)) {
            return $this->normalizeSpotlightUrl($options, $booleanOrUrl);
        }

        return filter_var($booleanOrUrl, \FILTER_VALIDATE_BOOLEAN);
    }

    private function normalizeSpotlightUrl(SymfonyOptions $options, string $url): string
    {
        if (substr_compare($url, '/stream', -7, 7) === 0) {
            return substr($url, 0, -7);
        }

        return $url;
    }

    private function normalizeDsnOption(SymfonyOptions $options, $value): ?Dsn
    {
        if ($value === null || \is_bool($value)) {
            return null;
        }

        if ($value instanceof Dsn) {
            return $value;
        }

        switch (strtolower($value)) {
            case '':
            case 'false':
            case '(false)':
            case 'empty':
            case '(empty)':
            case 'null':
            case '(null)':
                return null;
        }

        return Dsn::createFromString($value);
    }

    private function validateDsnOption($dsn): bool
    {
        if ($dsn === null || $dsn instanceof Dsn) {
            return true;
        }

        if (\is_bool($dsn)) {
            return $dsn === false;
        }

        switch (strtolower($dsn)) {
            case '':
            case 'false':
            case '(false)':
            case 'empty':
            case '(empty)':
            case 'null':
            case '(null)':
                return true;
        }

        try {
            Dsn::createFromString($dsn);

            return true;
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }

    private function validateMaxBreadcrumbsOptions(int $value): bool
    {
        return $value >= 0;
    }

    private function validateClassSerializersOption(array $serializers): bool
    {
        foreach ($serializers as $class => $serializer) {
            if (!\is_string($class) || !\is_callable($serializer)) {
                return false;
            }
        }

        return true;
    }

    private function validateContextLinesOption(?int $contextLines): bool
    {
        return $contextLines === null || $contextLines >= 0;
    }

    private function validateLogFlushThresholdOption(?int $logFlushThreshold): bool
    {
        return $logFlushThreshold === null || $logFlushThreshold > 0;
    }

    private function validateMetricFlushThresholdOption(?int $metricFlushThreshold): bool
    {
        return $metricFlushThreshold === null || $metricFlushThreshold > 0;
    }
}
