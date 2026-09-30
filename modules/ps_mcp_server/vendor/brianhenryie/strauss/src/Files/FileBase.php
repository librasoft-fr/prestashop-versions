<?php

namespace BrianHenryIE\Strauss\Files;

use BrianHenryIE\Strauss\Types\DiscoveredSymbol;

interface FileBase
{

    public function getSourcePath(string $relativeTo = ''): string;

    public function getAbsoluteTargetPath(string $relativeTo = ''): string;

    public function setAbsoluteTargetPath(string $absoluteTargetPath): void;

    public function isPhpFile(): bool;

    public function setDoCopy(bool $doCopy): void;

    public function isDoCopy(): bool;

    public function setDoPrefix(bool $doPrefix): void;

    public function isDoPrefix(): bool;

    
    public function setDoDelete(bool $doDelete): void;

    
    public function isDoDelete(): bool;

    public function setDidDelete(bool $didDelete): void;

    public function getDidDelete(): bool;

    public function addDiscoveredSymbol(DiscoveredSymbol $symbol): void;

    
    public function getDiscoveredSymbols(): array;

    public function getContents(): string;
}
