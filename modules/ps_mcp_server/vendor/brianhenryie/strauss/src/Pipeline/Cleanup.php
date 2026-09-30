<?php


namespace BrianHenryIE\Strauss\Pipeline;

use BrianHenryIE\Strauss\Composer\ComposerPackage;
use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use BrianHenryIE\Strauss\Files\File;
use BrianHenryIE\Strauss\Files\FileWithDependency;
use Composer\Json\JsonFile;
use FilesystemIterator;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Cleanup
{

    
    protected Filesystem $filesystem;

    protected string $workingDir;

    protected bool $isDeleteVendorFiles;
    protected bool $isDeleteVendorPackages;

    protected string $vendorDirectory = 'vendor'. DIRECTORY_SEPARATOR;
    protected string $targetDirectory;

    protected StraussConfig $config;

    public function __construct(StraussConfig $config, string $workingDir)
    {
        $this->config = $config;

        $this->vendorDirectory = $config->getVendorDirectory();
        $this->targetDirectory = $config->getTargetDirectory();
        $this->workingDir = $workingDir;

        $this->isDeleteVendorFiles = $config->isDeleteVendorFiles() && $config->getTargetDirectory() !== $config->getVendorDirectory();
        $this->isDeleteVendorPackages = $config->isDeleteVendorPackages() && $config->getTargetDirectory() !== $config->getVendorDirectory();

        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($workingDir));
    }

    
    public function cleanup(array $files): void
    {
        if (!$this->isDeleteVendorPackages && !$this->isDeleteVendorFiles) {
            return;
        }

        $sourceFiles = array_map(
            fn($file) => $file->getSourcePath($this->workingDir . $this->config->getVendorDirectory()),
            $files
        );

        if ($this->isDeleteVendorPackages) {
            $packages = [];
            foreach ($files as $file) {
                if ($file instanceof FileWithDependency) {
                    $packages[$file->getDependency()->getPackageName()] = $file->getDependency();
                }
            }

            
            foreach ($packages as $package) {
                
                if (str_starts_with($package->getPackageAbsolutePath(), $this->workingDir)) {
                    $packageRelativePath = str_replace($this->workingDir, '', $package->getPackageAbsolutePath());
                    $this->filesystem->deleteDirectory($packageRelativePath);
                } else {
                    
                    $symlinkPath =
                        rtrim(
                            $this->workingDir . $this->config->getVendorDirectory() . $package->getRelativePath(),
                            '/'
                        );

                    if (false !== strpos('WIN', PHP_OS)) {
                        
                        rmdir($symlinkPath);
                    } else {
                        unlink($symlinkPath);
                    }
                }
            }
        } elseif ($this->isDeleteVendorFiles) {
            foreach ($files as $file) {
                if (!$file->isDoDelete()) {
                    continue;
                }

                $sourceRelativePath = $file->getSourcePath($this->workingDir);

                $this->filesystem->delete($sourceRelativePath);

                $file->setDidDelete(true);
            }

            $this->cleanupFilesAutoloader();
        }

        
        $rootSourceDirectories = [];
        foreach ($sourceFiles as $sourceFile) {
            $arr = explode("/", $sourceFile, 2);
            $dir = $arr[0];
            $rootSourceDirectories[ $dir ] = $dir;
        }
        $rootSourceDirectories = array_map(
            function (string $path): string {
                return $this->vendorDirectory . $path;
            },
            array_keys($rootSourceDirectories)
        );

        foreach ($rootSourceDirectories as $rootSourceDirectory) {
            if (!is_dir($rootSourceDirectory) || is_link($rootSourceDirectory)) {
                continue;
            }

            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->workingDir . $rootSourceDirectory,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($it as $file) {
                if ($file->isDir() && $this->dirIsEmpty((string) $file)) {
                    rmdir((string)$file);
                }
            }
        }

        $this->cleanupInstalledJson();
    }

    
    protected function dirIsEmpty(string $dir): bool
    {
        $di = new RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        return iterator_count($di) === 0;
    }

    
    public function cleanupInstalledJson(): void
    {
        $installedJsonFile = new JsonFile($this->workingDir . '/vendor/composer/installed.json');
        if (!$installedJsonFile->exists()) {
            return;
        }
        $installedJsonArray = $installedJsonFile->read();

        foreach ($installedJsonArray['packages'] as $key => $package) {
            if (!isset($package['autoload'])) {
                continue;
            }
            $packageDir = $this->workingDir . $this->vendorDirectory . ltrim($package['install-path'], '.' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (!is_dir($packageDir)) {
                
                continue;
            }
            $autoload_key = $package['autoload'];
            foreach ($autoload_key as $type => $autoload) {
                switch ($type) {
                    case 'psr-4':
                        foreach ($autoload_key[$type] as $namespace => $dirs) {
                            if (is_array($dirs)) {
                                $autoload_key[$type][$namespace] = array_filter($dirs, function ($dir) use ($packageDir) {
                                    $dir = $packageDir . $dir;
                                    return is_readable($dir);
                                });
                            } else {
                                $dir = $packageDir . $dirs;
                                if (! is_readable($dir)) {
                                    unset($autoload_key[$type][$namespace]);
                                }
                            }
                        }
                        break;
                    default: 
                        $autoload_key[$type] = array_filter($autoload, function ($file) use ($packageDir) {
                            $filename = $packageDir . $file;
                            return file_exists($packageDir . $file);
                        });
                        break;
                }
            }
            $installedJsonArray['packages'][$key]['autoload'] = array_filter($autoload_key);
        }
        $installedJsonFile->write($installedJsonArray);
    }

    
    protected function cleanupFilesAutoloader(): void
    {
        if (! file_exists($this->workingDir . 'vendor/composer/autoload_files.php')) {
            return;
        }

        $files = include $this->workingDir . 'vendor/composer/autoload_files.php';

        $missingFiles = array();

        foreach ($files as $file) {
            if (! file_exists($file)) {
                $missingFiles[] = str_replace([ $this->workingDir, 'vendor/composer/../', 'vendor/' ], '', $file);
                
                
                
                $this->filesystem->write(
                    str_replace($this->workingDir, '', $file),
                    '<?php // This file was deleted by {@see https://github.com/BrianHenryIE/strauss}.'
                );
            }
        }

        if (empty($missingFiles)) {
            return;
        }

        $targetDirectory = $this->targetDirectory;

        foreach (array('autoload_static.php', 'autoload_files.php') as $autoloadFile) {
            $autoloadStaticPhp = $this->filesystem->read('vendor/composer/'.$autoloadFile);

            $autoloadStaticPhpAsArray = explode(PHP_EOL, $autoloadStaticPhp);

            $newAutoloadStaticPhpAsArray = array_map(
                function (string $line) use ($missingFiles, $targetDirectory): string {
                    $containsFile = array_reduce(
                        $missingFiles,
                        function (bool $carry, string $filepath) use ($line): bool {
                            return $carry || false !== strpos($line, $filepath);
                        },
                        false
                    );

                    if (!$containsFile) {
                        return $line;
                    }

                    
                    

                    return str_replace([
                        "=> __DIR__ . '/..' . '/",
                        "=> \$vendorDir . '/"
                    ], [
                        "=> __DIR__ . '/../../$targetDirectory' . '/",
                        "=> \$baseDir . '/$targetDirectory"
                    ], $line);
                },
                $autoloadStaticPhpAsArray
            );

            $newAutoloadStaticPhp = implode(PHP_EOL, $newAutoloadStaticPhpAsArray);

            $this->filesystem->write('vendor/composer/'.$autoloadFile, $newAutoloadStaticPhp);
        }
    }
}
