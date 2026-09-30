<?php



namespace Symfony\Component\Cache\Adapter;

use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Component\Cache\Traits\AbstractAdapterTrait;
use Symfony\Component\Cache\Traits\ContractsTrait;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


abstract class AbstractTagAwareAdapter implements TagAwareAdapterInterface, TagAwareCacheInterface, LoggerAwareInterface, ResettableInterface
{
    use AbstractAdapterTrait;
    use ContractsTrait;

    private const TAGS_PREFIX = "\1tags\1";

    protected function __construct(string $namespace = '', int $defaultLifetime = 0)
    {
        $this->namespace = '' === $namespace ? '' : CacheItem::validateKey($namespace).':';
        $this->defaultLifetime = $defaultLifetime;
        if (null !== $this->maxIdLength && \strlen($namespace) > $this->maxIdLength - 24) {
            throw new InvalidArgumentException(\sprintf('Namespace must be %d chars max, %d given ("%s").', $this->maxIdLength - 24, \strlen($namespace), $namespace));
        }
        self::$createCacheItem ??= \Closure::bind(
            static function ($key, $value, $isHit) {
                $item = new CacheItem();
                $item->key = $key;
                $item->isTaggable = true;
                
                if (!\is_array($value) || !\array_key_exists('value', $value)) {
                    return $item;
                }
                $item->isHit = $isHit;
                
                $item->value = $value['value'];
                $item->metadata[CacheItem::METADATA_TAGS] = isset($value['tags']) ? array_combine($value['tags'], $value['tags']) : [];
                if (isset($value['meta'])) {
                    
                    $v = unpack('Ve/Nc', $value['meta']);
                    $item->metadata[CacheItem::METADATA_EXPIRY] = $v['e'] + CacheItem::METADATA_EXPIRY_OFFSET;
                    $item->metadata[CacheItem::METADATA_CTIME] = $v['c'];
                }

                return $item;
            },
            null,
            CacheItem::class
        );
        self::$mergeByLifetime ??= \Closure::bind(
            static function ($deferred, &$expiredIds, $getId, $tagPrefix, $defaultLifetime) {
                $byLifetime = [];
                $now = microtime(true);
                $expiredIds = [];

                foreach ($deferred as $key => $item) {
                    $key = (string) $key;
                    if (null === $item->expiry) {
                        $ttl = 0 < $defaultLifetime ? $defaultLifetime : 0;
                    } elseif (!$item->expiry) {
                        $ttl = 0;
                    } elseif (0 >= $ttl = (int) (0.1 + $item->expiry - $now)) {
                        $expiredIds[] = $getId($key);
                        continue;
                    }
                    
                    if (isset(($metadata = $item->newMetadata)[CacheItem::METADATA_TAGS])) {
                        $value = ['value' => $item->value, 'tags' => $metadata[CacheItem::METADATA_TAGS]];
                        unset($metadata[CacheItem::METADATA_TAGS]);
                    } else {
                        $value = ['value' => $item->value, 'tags' => []];
                    }

                    if ($metadata) {
                        
                        $value['meta'] = pack('VN', (int) (0.1 + $metadata[CacheItem::METADATA_EXPIRY] - CacheItem::METADATA_EXPIRY_OFFSET), $metadata[CacheItem::METADATA_CTIME]);
                    }

                    
                    $value['tag-operations'] = ['add' => [], 'remove' => []];
                    $oldTags = $item->metadata[CacheItem::METADATA_TAGS] ?? [];
                    foreach (array_diff_key($value['tags'], $oldTags) as $addedTag) {
                        $value['tag-operations']['add'][] = $getId($tagPrefix.$addedTag);
                    }
                    foreach (array_diff_key($oldTags, $value['tags']) as $removedTag) {
                        $value['tag-operations']['remove'][] = $getId($tagPrefix.$removedTag);
                    }
                    $value['tags'] = array_keys($value['tags']);

                    $byLifetime[$ttl][$getId($key)] = $value;
                    $item->metadata = $item->newMetadata;
                }

                return $byLifetime;
            },
            null,
            CacheItem::class
        );
    }

    
    abstract protected function doSave(array $values, int $lifetime, array $addTagData = [], array $removeTagData = []): array;

    
    abstract protected function doDelete(array $ids): bool;

    
    abstract protected function doDeleteTagRelations(array $tagData): bool;

    
    abstract protected function doInvalidate(array $tagIds): bool;

    
    protected function doDeleteYieldTags(array $ids): iterable
    {
        foreach ($this->doFetch($ids) as $id => $value) {
            yield $id => \is_array($value) && \is_array($value['tags'] ?? null) ? $value['tags'] : [];
        }

        $this->doDelete($ids);
    }

    public function commit(): bool
    {
        $ok = true;
        $byLifetime = (self::$mergeByLifetime)($this->deferred, $expiredIds, $this->getId(...), self::TAGS_PREFIX, $this->defaultLifetime);
        $retry = $this->deferred = [];

        if ($expiredIds) {
            
            try {
                $this->doDelete($expiredIds);
            } catch (\Exception $e) {
                $ok = false;
                CacheItem::log($this->logger, 'Failed to delete expired items: '.$e->getMessage(), ['exception' => $e, 'cache-adapter' => get_debug_type($this)]);
            }
        }
        foreach ($byLifetime as $lifetime => $values) {
            try {
                $values = $this->extractTagData($values, $addTagData, $removeTagData);
                $e = $this->doSave($values, $lifetime, $addTagData, $removeTagData);
            } catch (\Exception $e) {
            }
            if (true === $e || [] === $e) {
                continue;
            }
            if (\is_array($e) || 1 === \count($values)) {
                foreach (\is_array($e) ? $e : array_keys($values) as $id) {
                    $ok = false;
                    $type = \array_key_exists($id, $values) ? get_debug_type($values[$id]) : 'unknown';
                    $message = \sprintf('Failed to save key "{key}" of type %s%s', $type, $e instanceof \Exception ? ': '.$e->getMessage() : '.');
                    CacheItem::log($this->logger, $message, ['key' => substr($id, \strlen($this->namespace)), 'exception' => $e instanceof \Exception ? $e : null, 'cache-adapter' => get_debug_type($this)]);
                }
            } else {
                foreach ($values as $id => $v) {
                    $retry[$lifetime][] = $id;
                }
            }
        }

        
        foreach ($retry as $lifetime => $ids) {
            foreach ($ids as $id) {
                try {
                    $v = $byLifetime[$lifetime][$id];
                    $values = $this->extractTagData([$id => $v], $addTagData, $removeTagData);
                    $e = $this->doSave($values, $lifetime, $addTagData, $removeTagData);
                } catch (\Exception $e) {
                }
                if (true === $e || [] === $e) {
                    continue;
                }
                $ok = false;
                $type = get_debug_type($v);
                $message = \sprintf('Failed to save key "{key}" of type %s%s', $type, $e instanceof \Exception ? ': '.$e->getMessage() : '.');
                CacheItem::log($this->logger, $message, ['key' => substr($id, \strlen($this->namespace)), 'exception' => $e instanceof \Exception ? $e : null, 'cache-adapter' => get_debug_type($this)]);
            }
        }

        return $ok;
    }

    public function deleteItems(array $keys): bool
    {
        if (!$keys) {
            return true;
        }

        $ok = true;
        $ids = [];
        $tagData = [];

        foreach ($keys as $key) {
            $ids[$key] = $this->getId($key);
            unset($this->deferred[$key]);
        }

        try {
            foreach ($this->doDeleteYieldTags(array_values($ids)) as $id => $tags) {
                foreach ($tags as $tag) {
                    $tagData[$this->getId(self::TAGS_PREFIX.$tag)][] = $id;
                }
            }
        } catch (\Exception) {
            $ok = false;
        }

        try {
            if ((!$tagData || $this->doDeleteTagRelations($tagData)) && $ok) {
                return true;
            }
        } catch (\Exception) {
        }

        
        foreach ($ids as $key => $id) {
            try {
                $e = null;
                if ($this->doDelete([$id])) {
                    continue;
                }
            } catch (\Exception $e) {
            }
            $message = 'Failed to delete key "{key}"'.($e instanceof \Exception ? ': '.$e->getMessage() : '.');
            CacheItem::log($this->logger, $message, ['key' => $key, 'exception' => $e, 'cache-adapter' => get_debug_type($this)]);
            $ok = false;
        }

        return $ok;
    }

    public function invalidateTags(array $tags): bool
    {
        if (!$tags) {
            return false;
        }

        $tagIds = [];
        foreach (array_unique($tags) as $tag) {
            $tagIds[] = $this->getId(self::TAGS_PREFIX.$tag);
        }

        try {
            if ($this->doInvalidate($tagIds)) {
                return true;
            }
        } catch (\Exception $e) {
            CacheItem::log($this->logger, 'Failed to invalidate tags: '.$e->getMessage(), ['exception' => $e, 'cache-adapter' => get_debug_type($this)]);
        }

        return false;
    }

    
    private function extractTagData(array $values, ?array &$addTagData, ?array &$removeTagData): array
    {
        $addTagData = $removeTagData = [];
        foreach ($values as $id => $value) {
            foreach ($value['tag-operations']['add'] as $tag => $tagId) {
                $addTagData[$tagId][] = $id;
            }

            foreach ($value['tag-operations']['remove'] as $tag => $tagId) {
                $removeTagData[$tagId][] = $id;
            }

            unset($values[$id]['tag-operations']);
        }

        return $values;
    }
}
