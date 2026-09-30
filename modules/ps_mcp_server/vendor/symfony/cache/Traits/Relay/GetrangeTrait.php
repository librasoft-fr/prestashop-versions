<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    
    trait GetrangeTrait
    {
        public function getrange($key, $start, $end): mixed
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getrange(...\func_get_args());
        }
    }
} else {
    
    trait GetrangeTrait
    {
        public function getrange($key, $start, $end): \Relay\Relay|false|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getrange(...\func_get_args());
        }
    }
}
