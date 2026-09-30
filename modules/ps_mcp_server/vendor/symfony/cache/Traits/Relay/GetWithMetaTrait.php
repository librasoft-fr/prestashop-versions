<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.10.1', '>=')) {
    
    trait GetWithMetaTrait
    {
        public function getWithMeta($key): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getWithMeta(...\func_get_args());
        }
    }
} else {
    
    trait GetWithMetaTrait
    {
    }
}
