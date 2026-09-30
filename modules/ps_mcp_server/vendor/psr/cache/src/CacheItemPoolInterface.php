<?php

namespace Psr\Cache;


interface CacheItemPoolInterface
{
    
    public function getItem(string $key): CacheItemInterface;

    
    public function getItems(array $keys = []): iterable;

    
    public function hasItem(string $key): bool;

    
    public function clear(): bool;

    
    public function deleteItem(string $key): bool;

    
    public function deleteItems(array $keys): bool;

    
    public function save(CacheItemInterface $item): bool;

    
    public function saveDeferred(CacheItemInterface $item): bool;

    
    public function commit(): bool;
}
