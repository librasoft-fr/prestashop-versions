<?php



namespace Symfony\Contracts\Cache;

use Psr\Cache\InvalidArgumentException;


interface NamespacedPoolInterface
{
    
    public function withSubNamespace(string $namespace): static;
}
