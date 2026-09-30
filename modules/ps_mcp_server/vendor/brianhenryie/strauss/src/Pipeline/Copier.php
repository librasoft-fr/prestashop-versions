<?php


namespace BrianHenryIE\Strauss\Pipeline;

use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use BrianHenryIE\Strauss\Files\DiscoveredFiles;
use BrianHenryIE\Strauss\Files\File;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Copier
{
    
    protected string $workingDir;

    protected string $absoluteTargetDir;

    protected DiscoveredFiles $files;

    
    protected Filesystem $filesystem;

    
    public function __construct(DiscoveredFiles $files, string $workingDir, StraussConfig $config)
    {
        $this->files = $files;

        $this->absoluteTargetDir = $workingDir . $config->getTargetDirectory();

        $this->filesystem = new Filesystem(new LocalFilesystemAdapter('/'), [
            Config::OPTION_DIRECTORY_VISIBILITY => 'public',
        ]);
    }

    
    public function prepareTarget(): void
    {
        if (! is_dir($this->absoluteTargetDir)) {
            $this->filesystem->createDirectory($this->absoluteTargetDir);
        } else {
            foreach ($this->files->getFiles() as $file) {
                if (!$file->isDoCopy()) {
                    continue;
                }

                $targetAbsoluteFilepath = $file->getAbsoluteTargetPath();

                if ($this->filesystem->fileExists($targetAbsoluteFilepath)) {
                    $this->filesystem->delete($targetAbsoluteFilepath);
                }
            }
        }
    }

    public function copy(): void
    {
        
        foreach ($this->files->getFiles() as $file) {
            if (!$file->isDoCopy()) {
                continue;
            }

            $sourceAbsoluteFilepath = $file->getSourcePath();

            $targetAbsolutePath = $file->getAbsoluteTargetPath();

            $this->filesystem->copy($sourceAbsoluteFilepath, $targetAbsolutePath);
        }
    }
}
