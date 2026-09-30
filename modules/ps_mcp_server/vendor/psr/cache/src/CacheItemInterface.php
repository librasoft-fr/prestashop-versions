<?php

namespace Psr\Cache;


interface CacheItemInterface
{
    
    public function getKey(): string;

    
    public function get(): mixed;

    
    public function isHit(): bool;

    
    public function set(mixed $value): static;

    
    public function expiresAt(?\DateTimeInterface $expiration): static;

    
    public function expiresAfter(int|\DateInterval|null $time): static;
}
