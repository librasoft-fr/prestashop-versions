<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.21.0', '>=')) {
    
    trait Relay21Trait
    {
        public function gcra($key, $maxBurst, $requestsPerPeriod, $period, $tokens = 0): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->gcra(...\func_get_args());
        }

        public function hotkeys($subcmd, $args = null): \Relay\Relay|array|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hotkeys(...\func_get_args());
        }
    }
} else {
    
    trait Relay21Trait
    {
    }
}
