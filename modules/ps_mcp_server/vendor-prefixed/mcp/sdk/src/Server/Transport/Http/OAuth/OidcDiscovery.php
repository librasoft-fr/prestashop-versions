<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Http\OAuth;

use PsMcpServerDeps\Http\Discovery\Psr17FactoryDiscovery;
use PsMcpServerDeps\Http\Discovery\Psr18ClientDiscovery;
use PsMcpServerDeps\Mcp\Exception\RuntimeException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\SimpleCache\CacheInterface;

class OidcDiscovery implements OidcDiscoveryInterface
{
    private const CACHE_KEY_PREFIX = 'mcp_oidc_discovery_';

    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private OidcDiscoveryMetadataPolicyInterface $metadataPolicy;

    public function __construct(
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        private readonly ?CacheInterface $cache = null,
        private readonly int $cacheTtl = 3600,
        ?OidcDiscoveryMetadataPolicyInterface $metadataPolicy = null,
    ) {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->metadataPolicy = $metadataPolicy ?? new StrictOidcDiscoveryMetadataPolicy();
    }

    public function getJwksUri(string $issuer): string
    {
        $metadata = $this->discover($issuer);

        return $metadata['jwks_uri'];
    }

    public function getTokenEndpoint(string $issuer): string
    {
        $metadata = $this->discover($issuer);

        return $metadata['token_endpoint'];
    }

    public function getAuthorizationEndpoint(string $issuer): string
    {
        $metadata = $this->discover($issuer);

        return $metadata['authorization_endpoint'];
    }

    public function discover(string $issuer): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . hash('sha256', $issuer);

        if (null !== $this->cache) {
            $cached = $this->cache->get($cacheKey);
            if (\is_array($cached)) {
                return $cached;
            }
        }

        $metadata = $this->fetchMetadata($issuer);

        if (null !== $this->cache) {
            $this->cache->set($cacheKey, $metadata, $this->cacheTtl);
        }

        return $metadata;
    }

    private function fetchMetadata(string $issuer): array
    {
        $issuer = rtrim($issuer, '/');
        $parsed = parse_url($issuer);

        if (false === $parsed || !isset($parsed['scheme'], $parsed['host'])) {
            throw new RuntimeException(\sprintf('Invalid issuer URL: %s', $issuer));
        }

        $scheme = $parsed['scheme'];
        $host = $parsed['host'];
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '';

        $baseUrl = $scheme . '://' . $host . $port;

        $discoveryUrls = [];

        if ('' !== $path && '/' !== $path) {
            $discoveryUrls[] = $baseUrl . '/.well-known/oauth-authorization-server' . $path;

            $discoveryUrls[] = $baseUrl . '/.well-known/openid-configuration' . $path;

            $discoveryUrls[] = $issuer . '/.well-known/openid-configuration';
        } else {
            $discoveryUrls[] = $baseUrl . '/.well-known/oauth-authorization-server';
            $discoveryUrls[] = $baseUrl . '/.well-known/openid-configuration';
        }

        $lastException = null;

        foreach ($discoveryUrls as $url) {
            try {
                $metadata = $this->fetchJson($url);
                if (!$this->metadataPolicy->isValid($metadata)) {
                    throw new RuntimeException(\sprintf('OIDC discovery response from %s has invalid format.', $url));
                }

                if (!isset($metadata['issuer']) || !\is_string($metadata['issuer'])) {
                    throw new RuntimeException(\sprintf('OIDC discovery response from %s is missing required "issuer" field.', $url));
                }
                if ($metadata['issuer'] !== $issuer) {
                    throw new RuntimeException(\sprintf('OIDC discovery issuer mismatch for %s: expected %s, got %s.', $url, $issuer, $metadata['issuer']));
                }

                return $metadata;
            } catch (RuntimeException $e) {
                $lastException = $e;
                continue;
            }
        }

        throw new RuntimeException(\sprintf('Failed to discover authorization server metadata for issuer: %s', $issuer), 0, $lastException);
    }

    private function fetchJson(string $url): array
    {
        $request = $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Accept', 'application/json');

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException(\sprintf('HTTP request to %s failed: %s', $url, $e->getMessage()), 0, $e);
        }

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException(\sprintf('HTTP request to %s failed with status %d', $url, $response->getStatusCode()));
        }

        try {
            $data = json_decode($response->getBody()->__toString(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException(\sprintf('Failed to decode JSON from %s: %s', $url, $e->getMessage()), 0, $e);
        }

        if (!\is_array($data)) {
            throw new RuntimeException(\sprintf('Expected JSON object from %s, got %s', $url, \gettype($data)));
        }

        return $data;
    }
}
