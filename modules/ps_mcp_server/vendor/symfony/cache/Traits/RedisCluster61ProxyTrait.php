<?php



namespace Symfony\Component\Cache\Traits;

if (version_compare(phpversion('redis'), '6.1.0-dev', '>')) {
    
    trait RedisCluster61ProxyTrait
    {
        public function getex($key, $options = []): \RedisCluster|string|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getex(...\func_get_args());
        }

        public function publish($channel, $message): \RedisCluster|bool|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->publish(...\func_get_args());
        }

        public function waitaof($key_or_address, $numlocal, $numreplicas, $timeout): \RedisCluster|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->waitaof(...\func_get_args());
        }
    }
} else {
    
    trait RedisCluster61ProxyTrait
    {
        public function publish($channel, $message): \RedisCluster|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->publish(...\func_get_args());
        }
    }
}
