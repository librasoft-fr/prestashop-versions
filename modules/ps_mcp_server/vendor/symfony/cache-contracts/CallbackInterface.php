<?php



namespace Symfony\Contracts\Cache;

use Psr\Cache\CacheItemInterface;


interface CallbackInterface
{
    
    public function __invoke(CacheItemInterface $item, bool &$save): mixed;
}
