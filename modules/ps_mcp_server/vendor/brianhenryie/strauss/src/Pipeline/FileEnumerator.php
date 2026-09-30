<?php


namespace BrianHenryIE\Strauss\Pipeline;

use BrianHenryIE\Strauss\Composer\ComposerPackage;
use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use BrianHenryIE\Strauss\Files\DiscoveredFiles;
use BrianHenryIE\Strauss\Files\File;
use BrianHenryIE\Strauss\Files\FileWithDependency;
use BrianHenryIE\Strauss\Helpers\Path;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Finder\Finder;

class FileEnumerator
{
    
    protected string $workingDir;

    
    protected string $vendorDir;

    
    protected array $excludePackageNames = array();

    
    protected array $excludeNamespaces = array();

    
    protected array $excludeFilePatterns = array();

    
    protected Filesystem $filesystem;

    protected DiscoveredFiles $discoveredFiles;

    
    protected array $filesAutoloaders = [];

    
    public function __construct(
        string $workingDir,
        StraussConfig $config
    ) {
        $this->discoveredFiles = new DiscoveredFiles();

        $this->workingDir = $workingDir;
        $this->vendorDir = $config->getVendorDirectory();

        $this->excludeNamespaces = $config->getExcludeNamespacesFromCopy();
        $this->excludePackageNames = $config->getExcludePackagesFromCopy();
        $this->excludeFilePatterns = $config->getExcludeFilePatternsFromCopy();

        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($this->workingDir));
    }

    
    public function compileFileListForDependencies(array $dependencies): DiscoveredFiles
    {
        foreach ($dependencies as $dependency) {
            if (in_array($dependency->getPackageName(), $this->excludePackageNames)) {
                continue;
            }

            
            $autoloaders = array_filter($dependency->getAutoload(), function ($type) {
                return 'exclude-from-classmap' !== $type;
            }, ARRAY_FILTER_USE_KEY);

            foreach ($autoloaders as $type => $value) {
                

                if ('files' === $type) {
                    
                    $this->filesAutoloaders[$dependency->getRelativePath()] = $value;
                }

                foreach ($value as $namespace => $namespace_relative_paths) {
                    if (!empty($namespace) && in_array($namespace, $this->excludeNamespaces)) {
                        continue;
                    }

                    if (! is_array($namespace_relative_paths)) {
                        $namespace_relative_paths = array( $namespace_relative_paths );
                    }

                    foreach ($namespace_relative_paths as $namespaceRelativePath) {
                        $sourceAbsolutePath = $dependency->getPackageAbsolutePath() . $namespaceRelativePath;

                        if (is_file($sourceAbsolutePath)) {
                            $this->addFileWithDependency($dependency, $namespaceRelativePath, $type);
                        } elseif (is_dir($sourceAbsolutePath)) {
                            
                            $sourcePath = Path::normalize($sourceAbsolutePath);


                            $finder = new Finder();
                            $finder->files()->in($sourcePath)->followLinks();

                            foreach ($finder as $foundFile) {
                                $sourceAbsoluteFilepath = $foundFile->getPathname();

                                
                                if (is_dir($sourceAbsoluteFilepath)) {
                                    continue;
                                }

                                $namespaceRelativePath = Path::normalize($namespaceRelativePath);

                                $this->addFileWithDependency(
                                    $dependency,
                                    $namespaceRelativePath . str_replace($sourcePath, '', $sourceAbsoluteFilepath),
                                    $type
                                );
                            }
                        }
                    }
                }
            }
        }

        return $this->discoveredFiles;
    }

    
    protected function addFileWithDependency(ComposerPackage $dependency, string $packageRelativePath, string $autoloaderType): void
    {
        $sourceAbsoluteFilepath = $dependency->getPackageAbsolutePath() . $packageRelativePath;
        $vendorRelativePath = $dependency->getRelativePath() . $packageRelativePath;
        $projectRelativePath    = $this->vendorDir . $vendorRelativePath;
        $isOutsideProjectDir    = 0 !== strpos($sourceAbsoluteFilepath, $this->workingDir);

        
        $f = $this->discoveredFiles->getFile($sourceAbsoluteFilepath)
            ?? new FileWithDependency($dependency, $vendorRelativePath, $sourceAbsoluteFilepath);

        $f->addAutoloader($autoloaderType);
        $f->setDoDelete($isOutsideProjectDir);

        foreach ($this->excludeFilePatterns as $excludePattern) {
            if (1 === preg_match($excludePattern, $vendorRelativePath)) {
                $f->setDoCopy(false);
            }
        }

        if ('<?php // This file was deleted by {@see https://github.com/BrianHenryIE/strauss}.'
            ===
            $this->filesystem->read($projectRelativePath)
        ) {
            $f->setDoCopy(false);
        }

        $this->discoveredFiles->add($f);
    }

    
    public function compileFileListForPaths(array $paths): DiscoveredFiles
    {
        $absoluteFilePaths = $this->findAllFilesAbsolutePaths($this->workingDir, $paths);

        foreach ($absoluteFilePaths as $sourceAbsolutePath) {
            $f = $this->discoveredFiles->getFile($sourceAbsolutePath)
                 ?? new File($sourceAbsolutePath);

            $this->discoveredFiles->add($f);
        }

        return $this->discoveredFiles;
    }

    
    public function findAllFilesAbsolutePaths(string $workingDir, array $relativeFileAndDirPaths, string $regexPattern = '/.+\.php$/'): array
    {
        $files = [];

        foreach ($relativeFileAndDirPaths as $path) {
            if (is_dir($path)) {
                $path = rtrim($path, '/').'/';
                $files = array_merge(
                    $files,
                    $this->findFilesInDirectory($this->workingDir, str_replace($this->workingDir, '', $path))
                );
            } elseif (is_file($path)) {
                $files[] = $path;
            } elseif (is_dir($this->workingDir . '/'. $path)) {
                $files = array_merge(
                    $files,
                    $this->findFilesInDirectory($this->workingDir, $path)
                );
            } elseif (is_file($this->workingDir . '/'. $path)) {
                $files[] = $this->workingDir . '/'. $path;
            }
        }

        return $files;
    }

    
    protected function findFilesInDirectory(string $workingDir, string $relativeDirectory = '.', string $regexPattern = '/.+\.php$/'): array
    {
        $dir = new RecursiveDirectoryIterator($workingDir . $relativeDirectory);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, $regexPattern, RegexIterator::GET_MATCH);
        $fileList = array();
        foreach ($files as $file) {
            $fileList = array_merge($fileList, str_replace($workingDir, '', $file));
        }
        return $fileList;
    }
}
