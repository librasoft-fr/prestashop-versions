<?php



namespace Symfony\Contracts\Cache;

use Psr\Cache\InvalidArgumentException;


interface TagAwareCacheInterface extends CacheInterface
{
    
    public function invalidateTags(array $tags): bool;
}
