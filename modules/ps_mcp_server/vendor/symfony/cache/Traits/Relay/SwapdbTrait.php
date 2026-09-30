<?php



namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    
    trait SwapdbTrait
    {
        public function swapdb($index1, $index2): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->swapdb(...\func_get_args());
        }
    }
} else {
    
    trait SwapdbTrait
    {
    }
}
