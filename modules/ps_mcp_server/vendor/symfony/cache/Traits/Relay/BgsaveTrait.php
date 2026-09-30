<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.11', '>=')) {
    
    trait BgsaveTrait
    {
        public function bgsave($arg = null): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bgsave(...\func_get_args());
        }
    }
} else {
    
    trait BgsaveTrait
    {
        public function bgsave($schedule = false): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bgsave(...\func_get_args());
        }
    }
}
