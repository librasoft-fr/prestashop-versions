<?php



namespace Symfony\Component\Cache\Traits;

class_alias(6.0 <= (float) phpversion('redis') ? Redis6Proxy::class : Redis5Proxy::class, RedisProxy::class);

if (false) {
    
    class RedisProxy extends \Redis
    {
    }
}
