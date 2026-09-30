<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    
    trait HsetTrait
    {
        public function hset($key, ...$keys_and_vals): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hset(...\func_get_args());
        }
    }
} else {
    
    trait HsetTrait
    {
        public function hset($key, $mem, $val, ...$kvals): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hset(...\func_get_args());
        }
    }
}
