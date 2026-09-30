<?php



namespace Symfony\Component\Cache\Traits;


class RedisClusterNodeProxy
{
    public function __construct(
        private array $host,
        private \RedisCluster $redis,
    ) {
    }

    public function __call(string $method, array $args)
    {
        return $this->redis->{$method}($this->host, ...$args);
    }

    public function scan(&$iIterator, $strPattern = null, $iCount = null)
    {
        return $this->redis->scan($iIterator, $this->host, $strPattern, $iCount);
    }

    public function getOption($name)
    {
        return $this->redis->getOption($name);
    }
}
