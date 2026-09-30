<?php


namespace BrianHenryIE\Strauss\Composer;

use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use Composer\Factory;
use Composer\IO\NullIO;

class ProjectComposerPackage extends ComposerPackage
{
    protected string $author;

    protected string $vendorDirectory;

    
    public function __construct(string $absolutePath, ?array $overrideAutoload = null)
    {
        $absolutePathFile = is_dir($absolutePath)
            ? rtrim($absolutePath, DIRECTORY_SEPARATOR) . '/composer.json'
            : $absolutePath;
        unset($absolutePath);

        $composer = Factory::create(new NullIO(), $absolutePathFile, true);

        parent::__construct($composer, $overrideAutoload);

        $authors = $this->composer->getPackage()->getAuthors();
        if (empty($authors) || !isset($authors[0]['name'])) {
            $this->author = explode("/", $this->packageName, 2)[0];
        } else {
            $this->author = $authors[0]['name'];
        }

        $this->vendorDirectory = is_string($this->composer->getConfig()->get('vendor-dir'))
            ? ltrim(str_replace(dirname($absolutePathFile), '', $this->composer->getConfig()->get('vendor-dir')), DIRECTORY_SEPARATOR)
            :  'vendor';
    }

    
    public function getStraussConfig(): StraussConfig
    {
        $config = new StraussConfig($this->composer);
        $config->setVendorDirectory($this->getVendorDirectory());
        return $config;
    }


    public function getAuthor(): string
    {
        return $this->author;
    }

    
    public function getVendorDirectory(): string
    {
        return rtrim($this->vendorDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    
    public function getFlatAutoloadKey(): array
    {
        $autoload = $this->getAutoload();
        $values = [];
        array_walk_recursive(
            $autoload,
            function ($value, $key) use (&$values) {
                $values[] = $value;
            }
        );
        return $values;
    }
}
