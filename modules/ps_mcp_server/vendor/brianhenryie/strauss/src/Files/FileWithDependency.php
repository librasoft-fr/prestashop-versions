<?php

namespace BrianHenryIE\Strauss\Files;

use BrianHenryIE\Strauss\Composer\ComposerPackage;

class FileWithDependency extends File implements HasDependency
{

    
    protected string $vendorRelativePath;

    
    protected ComposerPackage $dependency;

    
    protected array $autoloaderTypes = [];

    public function __construct(ComposerPackage $dependency, string $vendorRelativePath, string $sourceAbsolutePath)
    {
        parent::__construct($sourceAbsolutePath);

        $this->vendorRelativePath = $vendorRelativePath;
        $this->dependency         = $dependency;
    }

    public function getDependency(): ComposerPackage
    {
        return $this->dependency;
    }

    

    
    public function addAutoloader(string $autoloaderType): void
    {
        $this->autoloaderTypes = array_unique(array_merge($this->autoloaderTypes, array($autoloaderType)));
    }

    public function isFilesAutoloaderFile(): bool
    {
        return in_array('files', $this->autoloaderTypes, true);
    }

    public function getVendorRelativePath(): string
    {
        return $this->vendorRelativePath;
    }
}
