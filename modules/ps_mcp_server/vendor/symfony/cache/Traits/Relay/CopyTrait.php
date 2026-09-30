<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.8.1', '>=')) {
    
    trait CopyTrait
    {
        public function copy($src, $dst, $options = null): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->copy(...\func_get_args());
        }
    }
} else {
    
    trait CopyTrait
    {
        public function copy($src, $dst, $options = null): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->copy(...\func_get_args());
        }
    }
}
