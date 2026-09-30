<?php

namespace BrianHenryIE\Strauss\Files;

use BrianHenryIE\Strauss\Types\DiscoveredSymbol;

class File implements FileBase
{
    
    protected string $sourceAbsolutePath;


    
    protected bool $doCopy = true;

    
    protected bool $doDelete = false;

    
    protected array $discoveredSymbols = [];

    protected string $absoluteTargetPath;

    protected bool $didDelete = false;

    public function __construct(string $sourceAbsolutePath)
    {
        $this->sourceAbsolutePath = $sourceAbsolutePath;
    }

    public function getSourcePath(string $relativeTo = ''): string
    {
        return str_replace($relativeTo, '', $this->sourceAbsolutePath);
    }

    public function isPhpFile(): bool
    {
        return substr($this->sourceAbsolutePath, -4) === '.php';
    }

    
    public function setDoCopy(bool $doCopy): void
    {
        $this->doCopy = $doCopy;
    }
    public function isDoCopy(): bool
    {
        return $this->doCopy;
    }

    public function setDoPrefix(bool $doPrefix): void
    {
    }

    
    public function isDoPrefix(): bool
    {
        return true;
    }

    
    public function setDoDelete(bool $doDelete): void
    {
        $this->doDelete = $doDelete;
    }

    
    public function isDoDelete(): bool
    {
        return $this->doDelete;
    }

    public function setDidDelete(bool $didDelete): void
    {
        $this->didDelete = $didDelete;
    }
    public function getDidDelete(): bool
    {
        return $this->didDelete;
    }

    public function addDiscoveredSymbol(DiscoveredSymbol $symbol): void
    {
        $this->discoveredSymbols[$symbol->getOriginalSymbol()] = $symbol;
    }

    
    public function getDiscoveredSymbols(): array
    {
        return $this->discoveredSymbols;
    }

    public function setAbsoluteTargetPath(string $absoluteTargetPath): void
    {
        $this->absoluteTargetPath = $absoluteTargetPath;
    }

    
    public function getAbsoluteTargetPath(string $relativeTo = ''): string
    {
        return isset($this->absoluteTargetPath)
            ? str_replace($relativeTo, '', $this->absoluteTargetPath)
            : $this->getSourcePath($relativeTo);
    }

    public function getContents(): string
    {
        
        

        $contents = file_get_contents($this->sourceAbsolutePath);
        if (false === $contents) {
            throw new \Exception("Failed to read file at {$this->sourceAbsolutePath}");
        }

        return $contents;
    }

    protected bool $didUpdate = false;
    public function setDidUpdate(): void
    {
        $this->didUpdate = true;
    }
    public function getDidUpdate(): bool
    {
        return $this->didUpdate;
    }
}
