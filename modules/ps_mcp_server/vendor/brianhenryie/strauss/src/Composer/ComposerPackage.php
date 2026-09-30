<?php


namespace BrianHenryIE\Strauss\Composer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;


class ComposerPackage
{
    
    protected \Composer\Composer $composer;

    
    protected string $packageName;

    
    protected ?string $relativePath = null;

    
    protected ?string $packageAbsolutePath = null;

    
    protected array $autoload = [];

    
    protected array $requiresNames = [];

    protected string $license;

    
    public static function fromFile(string $absolutePath, array $overrideAutoload = null): ComposerPackage
    {
        if (is_dir($absolutePath)) {
            $absolutePath = rtrim($absolutePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer.json';
        }

        $composer = Factory::create(new NullIO(), $absolutePath, true);

        return new ComposerPackage($composer, $overrideAutoload);
    }

    
    public static function fromComposerJsonArray($jsonArray, array $overrideAutoload = null): ComposerPackage
    {
        $factory = new Factory();
        $io = new NullIO();
        $composer = $factory->createComposer($io, $jsonArray, true);

        return new ComposerPackage($composer, $overrideAutoload);
    }

    
    public function __construct(Composer $composer, array $overrideAutoload = null)
    {
        $this->composer = $composer;

        $this->packageName = $composer->getPackage()->getName();

        $composerJsonFileAbsolute = $composer->getConfig()->getConfigSource()->getName();

        $absolutePath = realpath(dirname($composerJsonFileAbsolute));
        if (false !== $absolutePath) {
            $this->packageAbsolutePath = $absolutePath . DIRECTORY_SEPARATOR;
        }

        $vendorDirectory = $this->composer->getConfig()->get('vendor-dir');
        if (file_exists($vendorDirectory . DIRECTORY_SEPARATOR . $this->packageName)) {
            $this->relativePath = $this->packageName;
            $this->packageAbsolutePath = realpath($vendorDirectory . DIRECTORY_SEPARATOR . $this->packageName) . DIRECTORY_SEPARATOR;
        
        } elseif (0 !== strpos($absolutePath, getcwd()) && 1 === preg_match('/.*[\/\\\\]([^\/\\\\]*[\/\\\\][^\/\\\\]*)[\/\\\\][^\/\\\\]*/', $vendorDirectory, $output_array)) {
            $this->relativePath = $output_array[1];
        } elseif (1 === preg_match('/.*[\/\\\\]([^\/\\\\]+[\/\\\\][^\/\\\\]+)[\/\\\\]composer.json/', $composerJsonFileAbsolute, $output_array)) {
        
            $this->relativePath = $output_array[1];
        }

        if (!is_null($overrideAutoload)) {
            $composer->getPackage()->setAutoload($overrideAutoload);
        }

        $this->autoload = $composer->getPackage()->getAutoload();

        foreach ($composer->getPackage()->getRequires() as $_name => $packageLink) {
            $this->requiresNames[] = $packageLink->getTarget();
        }

        
        $this->license = !empty($composer->getPackage()->getLicense())
            ? implode(',', $composer->getPackage()->getLicense())
            : 'proprietary?';
    }

    
    public function getPackageName(): string
    {
        return $this->packageName;
    }

    
    public function getRelativePath(): ?string
    {
        return $this->relativePath . DIRECTORY_SEPARATOR;
    }

    public function getPackageAbsolutePath(): ?string
    {
        return $this->packageAbsolutePath;
    }

    
    public function getAutoload(): array
    {
        return $this->autoload;
    }

    
    public function getRequiresNames(): array
    {
        
        $removePhpExt = function ($element) {
            return !( 0 === strpos($element, 'ext') || 'php' === $element );
        };

        return array_filter($this->requiresNames, $removePhpExt);
    }

    public function getLicense():string
    {
        return $this->license;
    }
}
