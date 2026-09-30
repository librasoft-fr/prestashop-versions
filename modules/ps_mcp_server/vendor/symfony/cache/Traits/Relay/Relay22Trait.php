<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.22.0', '>=')) {
    
    trait Relay22Trait
    {
        public function jsonStrAppend($key, $value, $path = null, $fpha = null): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->jsonStrAppend(...\func_get_args());
        }
    }
} else {
    
    trait Relay22Trait
    {
        public function jsonStrAppend($key, $value, $path = null): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->jsonStrAppend(...\func_get_args());
        }
    }
}
