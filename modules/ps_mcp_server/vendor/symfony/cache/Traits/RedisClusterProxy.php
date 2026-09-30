<?php



namespace Symfony\Component\Cache\Traits;

class_alias(6.0 <= (float) phpversion('redis') ? RedisCluster6Proxy::class : RedisCluster5Proxy::class, RedisClusterProxy::class);

if (false) {
    
    class RedisClusterProxy extends \RedisCluster
    {
    }
}
