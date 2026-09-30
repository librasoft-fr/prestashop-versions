<?php



namespace Symfony\Component\Cache;


interface PruneableInterface
{
    public function prune(): bool;
}
