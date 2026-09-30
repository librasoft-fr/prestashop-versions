<?php

namespace BrianHenryIE\Strauss\Files;

use BrianHenryIE\Strauss\Composer\ComposerPackage;

interface HasDependency
{

    public function getDependency(): ComposerPackage;

    
    public function addAutoloader(string $autoloaderType): void;

    public function isFilesAutoloaderFile(): bool;
}
