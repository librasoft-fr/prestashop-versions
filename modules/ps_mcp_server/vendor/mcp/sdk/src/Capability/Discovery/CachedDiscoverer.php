<?php



namespace Mcp\Capability\Discovery;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;


final class CachedDiscoverer implements DiscovererInterface
{
    private const CACHE_PREFIX = 'mcp_discovery_';

    public function __construct(
        private readonly DiscovererInterface $discoverer,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    
    public function discover(string $basePath, array $directories, array $excludeDirs = []): DiscoveryState
    {
        $cacheKey = $this->generateCacheKey($basePath, $directories, $excludeDirs);

        $cachedResult = $this->cache->get($cacheKey);
        if (null !== $cachedResult) {
            $this->logger->debug('Using cached discovery results', [
                'cache_key' => $cacheKey,
                'base_path' => $basePath,
                'directories' => $directories,
            ]);

            return $cachedResult;
        }

        $this->logger->debug('Cache miss, performing fresh discovery', [
            'cache_key' => $cacheKey,
            'base_path' => $basePath,
            'directories' => $directories,
        ]);

        $discoveryState = $this->discoverer->discover($basePath, $directories, $excludeDirs);

        $this->cache->set($cacheKey, $discoveryState);

        return $discoveryState;
    }

    
    private function generateCacheKey(string $basePath, array $directories, array $excludeDirs): string
    {
        $keyData = [
            'base_path' => $basePath,
            'directories' => $directories,
            'exclude_dirs' => $excludeDirs,
        ];

        return self::CACHE_PREFIX.md5(serialize($keyData));
    }

    
    public function clearCache(): void
    {
        $this->cache->clear();
        $this->logger->info('Discovery cache cleared');
    }
}
