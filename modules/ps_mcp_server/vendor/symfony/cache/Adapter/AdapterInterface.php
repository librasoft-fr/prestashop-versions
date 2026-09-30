<?php



namespace Symfony\Component\Cache\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;


class_exists(CacheItem::class);


interface AdapterInterface extends CacheItemPoolInterface
{
    public function getItem(mixed $key): CacheItem;

    
    public function getItems(array $keys = []): iterable;

    public function clear(string $prefix = ''): bool;
}
