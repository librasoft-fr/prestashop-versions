<?php


namespace BrianHenryIE\Strauss\Pipeline;

use BrianHenryIE\Strauss\Composer\ComposerPackage;
use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class DependenciesEnumerator
{
    
    protected string $workingDir;

    
    protected string $vendorDir;

    
    protected array $requiredPackageNames;

    
    protected Filesystem $filesystem;

    
    protected array $virtualPackages = array(
        'php-http/client-implementation'
    );

    
    protected array $flatDependencyTree = array();

    
    protected array $filesAutoloaders = [];

    
    protected array $overrideAutoload = array();

    
    public function __construct(
        string $workingDir,
        StraussConfig $config
    ) {
        $this->workingDir = $workingDir;
        $this->vendorDir = $config->getVendorDirectory();
        $this->overrideAutoload = $config->getOverrideAutoload();
        $this->requiredPackageNames = $config->getPackages();

        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($this->workingDir));
    }

    
    public function getAllDependencies(): array
    {
        $this->recursiveGetAllDependencies($this->requiredPackageNames);
        return $this->flatDependencyTree;
    }

    
    protected function recursiveGetAllDependencies(array $requiredPackageNames): void
    {
        $requiredPackageNames = array_filter($requiredPackageNames, array( $this, 'removeVirtualPackagesFilter' ));

        foreach ($requiredPackageNames as $requiredPackageName) {
            
            if (isset($this->flatDependencyTree[$requiredPackageName])) {
                continue;
            }

            $packageComposerFile = $this->workingDir . $this->vendorDir
                                   . $requiredPackageName . DIRECTORY_SEPARATOR . 'composer.json';

            $overrideAutoload = $this->overrideAutoload[ $requiredPackageName ] ?? null;

            if (file_exists($packageComposerFile)) {
                $requiredComposerPackage = ComposerPackage::fromFile($packageComposerFile, $overrideAutoload);
            } else {
                $fileContents           = file_get_contents($this->workingDir . 'composer.lock');
                if (false === $fileContents) {
                    throw new Exception('Failed to read contents of ' . $this->workingDir . 'composer.lock');
                }
                $composerLock           = json_decode($fileContents, true);
                $requiredPackageComposerJson = null;
                foreach ($composerLock['packages'] as $packageJson) {
                    if ($requiredPackageName === $packageJson['name']) {
                        $requiredPackageComposerJson = $packageJson;
                        break;
                    }
                }
                if (is_null($requiredPackageComposerJson)) {
                    
                    continue;
                }

                $requiredComposerPackage = ComposerPackage::fromComposerJsonArray($requiredPackageComposerJson, $overrideAutoload);
            }

            $this->flatDependencyTree[$requiredComposerPackage->getPackageName()] = $requiredComposerPackage;
            $nextRequiredPackageNames                                             = $requiredComposerPackage->getRequiresNames();

            $this->recursiveGetAllDependencies($nextRequiredPackageNames);
        }
    }

    
    public function getAllFilesAutoloaders(): array
    {
        $filesAutoloaders = array();
        foreach ($this->flatDependencyTree as $packageName => $composerPackage) {
            if (isset($composerPackage->getAutoload()['files'])) {
                $filesAutoloaders[$packageName] = $composerPackage->getAutoload()['files'];
            }
        }
        return $filesAutoloaders;
    }

    
    protected function removeVirtualPackagesFilter(string $requiredPackageName): bool
    {
        return ! (
            0 === strpos($requiredPackageName, 'ext')
            || 'php' === $requiredPackageName
            || in_array($requiredPackageName, $this->virtualPackages)
        );
    }
}
