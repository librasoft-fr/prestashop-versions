<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    
    trait MoveTrait
    {
        public function blmove($srckey, $dstkey, $srcpos, $dstpos, $timeout): mixed
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->blmove(...\func_get_args());
        }

        public function lmove($srckey, $dstkey, $srcpos, $dstpos): mixed
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lmove(...\func_get_args());
        }
    }
} else {
    
    trait MoveTrait
    {
        public function blmove($srckey, $dstkey, $srcpos, $dstpos, $timeout): \Relay\Relay|false|string|null
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->blmove(...\func_get_args());
        }

        public function lmove($srckey, $dstkey, $srcpos, $dstpos): \Relay\Relay|false|string|null
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lmove(...\func_get_args());
        }
    }
}
