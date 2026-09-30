<?php



namespace Symfony\Contracts\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;


interface CacheInterface
{
    
    public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null): mixed;

    
    public function delete(string $key): bool;
}
