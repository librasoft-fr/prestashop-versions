<?php

declare(strict_types=1);

namespace Sentry;


final class Dsn implements \Stringable
{
    
    private const SENTRY_ORG_ID_REGEX = '/^o(\d+)\./';

    
    private $scheme;

    
    private $host;

    
    private $port;

    
    private $publicKey;

    
    private $projectId;

    
    private $path;

    
    private $orgId;

    
    private function __construct(string $scheme, string $host, int $port, string $projectId, string $path, string $publicKey, ?int $orgId = null)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->projectId = $projectId;
        $this->path = $path;
        $this->publicKey = $publicKey;
        $this->orgId = $orgId;
    }

    
    public static function createFromString(string $value): self
    {
        $parsedDsn = parse_url($value);

        if ($parsedDsn === false) {
            throw new \InvalidArgumentException(\sprintf('The "%s" DSN is invalid.', $value));
        }

        foreach (['scheme', 'host', 'path', 'user'] as $component) {
            if (!isset($parsedDsn[$component]) || (isset($parsedDsn[$component]) && empty($parsedDsn[$component]))) {
                throw new \InvalidArgumentException(\sprintf('The "%s" DSN must contain a scheme, a host, a user and a path component.', $value));
            }
        }

        if (!\in_array($parsedDsn['scheme'], ['http', 'https'], true)) {
            throw new \InvalidArgumentException(\sprintf('The scheme of the "%s" DSN must be either "http" or "https".', $value));
        }

        $segmentPaths = explode('/', $parsedDsn['path']);
        $projectId = array_pop($segmentPaths);
        $lastSlashPosition = strrpos($parsedDsn['path'], '/');
        $path = $parsedDsn['path'];

        if ($lastSlashPosition !== false) {
            $path = substr($parsedDsn['path'], 0, $lastSlashPosition);
        }

        $orgId = null;
        if (preg_match(self::SENTRY_ORG_ID_REGEX, $parsedDsn['host'], $matches) == 1) {
            $orgId = (int) $matches[1];
        }

        return new self(
            $parsedDsn['scheme'],
            $parsedDsn['host'],
            $parsedDsn['port'] ?? ($parsedDsn['scheme'] === 'http' ? 80 : 443),
            $projectId,
            $path,
            $parsedDsn['user'],
            $orgId
        );
    }

    
    public function getScheme(): string
    {
        return $this->scheme;
    }

    
    public function getHost(): string
    {
        return $this->host;
    }

    
    public function getPort(): int
    {
        return $this->port;
    }

    
    public function getPath(): string
    {
        return $this->path;
    }

    
    public function getProjectId(): string
    {
        return $this->projectId;
    }

    
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getOrgId(): ?int
    {
        return $this->orgId;
    }

    
    public function getEnvelopeApiEndpointUrl(): string
    {
        return $this->getBaseEndpointUrl() . '/envelope/';
    }

    
    public function getCspReportEndpointUrl(): string
    {
        return $this->getBaseEndpointUrl() . '/security/?sentry_key=' . $this->publicKey;
    }

    
    public function getOtlpTracesEndpointUrl(): string
    {
        return $this->getBaseEndpointUrl() . '/integration/otlp/v1/traces/';
    }

    
    public function __toString(): string
    {
        $url = $this->scheme . '://' . $this->publicKey;

        $url .= '@' . $this->host;

        if (($this->scheme === 'http' && $this->port !== 80) || ($this->scheme === 'https' && $this->port !== 443)) {
            $url .= ':' . $this->port;
        }

        if ($this->path !== null) {
            $url .= $this->path;
        }

        $url .= '/' . $this->projectId;

        return $url;
    }

    
    private function getBaseEndpointUrl(): string
    {
        $url = $this->scheme . '://' . $this->host;

        if (($this->scheme === 'http' && $this->port !== 80) || ($this->scheme === 'https' && $this->port !== 443)) {
            $url .= ':' . $this->port;
        }

        if ($this->path !== null) {
            $url .= $this->path;
        }

        $url .= '/api/' . $this->projectId;

        return $url;
    }
}
