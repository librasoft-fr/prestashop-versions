<?php



namespace Symfony\Component\VarExporter;

interface LazyObjectInterface
{
    
    public function isLazyObjectInitialized(bool $partial = false): bool;

    
    public function initializeLazyObject(): object;

    
    public function resetLazyObject(): bool;
}
