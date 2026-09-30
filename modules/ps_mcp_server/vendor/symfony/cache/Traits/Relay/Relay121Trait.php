<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.12.1', '>=')) {
    
    trait Relay121Trait
    {
        public function hgetWithMeta($hash, $member): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hgetWithMeta(...\func_get_args());
        }

        public function select($db): \Relay\Relay|bool|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->select(...\func_get_args());
        }

        public function watch($key, ...$other_keys): \Relay\Relay|bool|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->watch(...\func_get_args());
        }
    }
} else {
    
    trait Relay121Trait
    {
        public function select($db): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->select(...\func_get_args());
        }

        public function watch($key, ...$other_keys): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->watch(...\func_get_args());
        }
    }
}
