<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.20.0', '>=')) {
    
    trait Relay20Trait
    {
        public function _digest($value): string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_digest(...\func_get_args());
        }

        public function delex($key, $options = null): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->delex(...\func_get_args());
        }

        public function digest($key): \Relay\Relay|false|string|null
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->digest(...\func_get_args());
        }

        public function msetex($kvals, $ttl = null): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->msetx(...\func_get_args());
        }
    }
} else {
    
    trait Relay20Trait
    {
    }
}
