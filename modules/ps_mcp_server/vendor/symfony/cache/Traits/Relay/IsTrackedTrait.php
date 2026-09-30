<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.11.1', '>=')) {
    
    trait IsTrackedTrait
    {
        public function isTracked($key): bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->isTracked(...\func_get_args());
        }
    }
} else {
    
    trait IsTrackedTrait
    {
    }
}
