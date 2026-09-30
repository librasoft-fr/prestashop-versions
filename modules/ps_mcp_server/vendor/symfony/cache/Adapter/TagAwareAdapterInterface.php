<?php



namespace Symfony\Component\Cache\Adapter;

use Psr\Cache\InvalidArgumentException;


interface TagAwareAdapterInterface extends AdapterInterface
{
    
    public function invalidateTags(array $tags): bool;
}
