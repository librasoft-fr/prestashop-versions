<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    
    trait GeosearchTrait
    {
        public function geosearch($key, $position, $shape, $unit, $options = []): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geosearch(...\func_get_args());
        }
    }
} else {
    
    trait GeosearchTrait
    {
        public function geosearch($key, $position, $shape, $unit, $options = []): \Relay\Relay|array
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geosearch(...\func_get_args());
        }
    }
}
