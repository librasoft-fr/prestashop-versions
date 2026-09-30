<?php

namespace BrianHenryIE\Strauss\Config;

interface FileCopyScannerConfigInterface
{

    public function getVendorDirectory(): string;

    
    public function getExcludePackagesFromCopy(): array;

    
    public function getExcludeNamespacesFromCopy(): array;

    
    public function getExcludeFilePatternsFromCopy(): array;

    public function isDeleteVendorFiles(): bool;

    public function getTargetDirectory(): string;
}
