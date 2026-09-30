<?php



namespace Mcp\Server\Transport\Http\OAuth;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Mcp\Exception\RuntimeException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\SimpleCache\CacheInterface;


class JwksProvider implements JwksProviderInterface
{
    private const CACHE_KEY_PREFIX = 'mcp_jwks_';

    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;

    
    public function __construct(
        private readonly OidcDiscoveryInterface $discovery,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        private readonly ?CacheInterface $cache = null,
        private readonly int $cacheTtl = 3600,
    ) {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    
    public function getJwks(string $issuer, ?string $jwksUri = null): array
    {
        $jwksUri ??= $this->discovery->getJwksUri($issuer);
        $cacheKey = self::CACHE_KEY_PREFIX.hash('sha256', $jwksUri);

        if (null !== $this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($this->isJwksValid($cached)) {
                
                return $cached;
            }
        }

        $jwks = $this->fetchJwks($jwksUri);

        if (!$this->isJwksValid($jwks)) {
            throw new RuntimeException(\sprintf('JWKS response from %s has invalid format: expected non-empty "keys" array.', $jwksUri));
        }

        if (null !== $this->cache) {
            $this->cache->set($cacheKey, $jwks, $this->cacheTtl);
        }

        return $jwks;
    }

    
    private function fetchJwks(string $jwksUri): array
    {
        $request = $this->requestFactory->createRequest('GET', $jwksUri)
            ->withHeader('Accept', 'application/json');

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException(\sprintf('Failed to fetch JWKS from %s: %s', $jwksUri, $e->getMessage()), 0, $e);
        }

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException(\sprintf('Failed to fetch JWKS from %s: HTTP %d', $jwksUri, $response->getStatusCode()));
        }

        try {
            $data = json_decode($response->getBody()->__toString(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException(\sprintf('Failed to decode JWKS: %s', $e->getMessage()), 0, $e);
        }

        if (!\is_array($data)) {
            throw new RuntimeException('Invalid JWKS format: expected JSON object.');
        }

        return $data;
    }

    private function isJwksValid(mixed $jwks): bool
    {
        if (!\is_array($jwks) || !isset($jwks['keys']) || !\is_array($jwks['keys'])) {
            return false;
        }

        $nonEmptyKeys = array_filter($jwks['keys'], static fn (mixed $key): bool => \is_array($key) && [] !== $key);

        return [] !== $nonEmptyKeys;
    }
}
