<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

use PsMcpServerDeps\Sentry\HttpClient\HttpClient;
use PsMcpServerDeps\Sentry\HttpClient\HttpClientInterface;
use PsMcpServerDeps\Sentry\Serializer\PayloadSerializer;
use PsMcpServerDeps\Sentry\Serializer\RepresentationSerializerInterface;
use PsMcpServerDeps\Sentry\Transport\HttpTransport;
use PsMcpServerDeps\Sentry\Transport\TransportInterface;
use Psr\Log\LoggerInterface;

final class ClientBuilder
{
    private $options;

    private $transport;

    private $httpClient;

    private $representationSerializer;

    private $logger;

    private $sdkIdentifier = Client::SDK_IDENTIFIER;

    private $sdkVersion = Client::SDK_VERSION;

    public function __construct(?Options $options = null)
    {
        $this->options = $options ?? new Options();
    }

    public static function create(array $options = []): self
    {
        return new self(new Options($options));
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function setRepresentationSerializer(RepresentationSerializerInterface $representationSerializer): self
    {
        $this->representationSerializer = $representationSerializer;

        return $this;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger ?? $this->options->getLogger();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function setSdkIdentifier(string $sdkIdentifier): self
    {
        $this->sdkIdentifier = $sdkIdentifier;

        return $this;
    }

    public function setSdkVersion(string $sdkVersion): self
    {
        $this->sdkVersion = $sdkVersion;

        return $this;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport
            ?? $this->options->getTransport()
            ?? new HttpTransport(
                $this->options,
                $this->getHttpClient(),
                new PayloadSerializer($this->options),
                $this->getLogger()
            );
    }

    public function setTransport(TransportInterface $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient
            ?? $this->options->getHttpClient()
            ?? new HttpClient($this->sdkIdentifier, $this->sdkVersion);
    }

    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function getClient(): ClientInterface
    {
        return new Client(
            $this->options,
            $this->getTransport(),
            $this->sdkIdentifier,
            $this->sdkVersion,
            $this->representationSerializer,
            $this->getLogger()
        );
    }
}
