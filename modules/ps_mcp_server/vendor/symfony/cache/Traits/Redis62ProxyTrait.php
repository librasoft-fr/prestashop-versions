<?php



namespace Symfony\Component\Cache\Traits;

if (version_compare(phpversion('redis'), '6.2.0', '>=')) {
    
    trait Redis62ProxyTrait
    {
        public function expiremember($key, $field, $ttl, $unit = null): \Redis|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->expiremember(...\func_get_args());
        }

        public function expirememberat($key, $field, $timestamp): \Redis|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->expirememberat(...\func_get_args());
        }

        public function getWithMeta($key): \Redis|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getWithMeta(...\func_get_args());
        }

        public function serverName(): false|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->serverName(...\func_get_args());
        }

        public function serverVersion(): false|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->serverVersion(...\func_get_args());
        }
    }
} else {
    
    trait Redis62ProxyTrait
    {
    }
}
