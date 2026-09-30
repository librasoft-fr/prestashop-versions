<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    
    trait PfcountTrait
    {
        public function pfcount($key_or_keys): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pfcount(...\func_get_args());
        }
    }
} else {
    
    trait PfcountTrait
    {
        public function pfcount($key): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pfcount(...\func_get_args());
        }
    }
}
