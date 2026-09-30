<?php


namespace BrianHenryIE\Strauss\Composer\Extra;

use BrianHenryIE\Strauss\Config\ChangeEnumeratorConfigInterface;
use BrianHenryIE\Strauss\Config\FileCopyScannerConfigInterface;
use BrianHenryIE\Strauss\Config\FileSymbolScannerConfigInterface;
use Composer\Composer;
use Exception;
use JsonMapper\JsonMapperFactory;
use JsonMapper\Middleware\Rename\Rename;
use Symfony\Component\Console\Input\InputInterface;

class StraussConfig implements
    ReplaceConfigInterface,
    FileSymbolScannerConfigInterface,
    FileCopyScannerConfigInterface,
    ChangeEnumeratorConfigInterface
{
    
    protected string $targetDirectory = 'vendor-prefixed';

    
    protected string $vendorDirectory = 'vendor';

    
    protected ?string $namespacePrefix = null;

    
    protected ?string $classmapPrefix = null;

    
    protected ?string $constantsPrefix = null;

    
    protected ?array $updateCallSites = null;

    
    protected array $packages = [];

    
    private array $excludePackages;

    
    protected array $excludeFromCopy = array('file_patterns'=>array(),'namespaces'=>array(),'packages'=>array());

    
    protected array $excludeFromPrefix = array('file_patterns'=>array(),'namespaces'=>array(),'packages'=>array());

    
    protected array $overrideAutoload = [];

    
    protected bool $deleteVendorFiles = false;

    
    protected bool $deleteVendorPackages = false;

    protected bool $classmapOutput;

    
    protected array $namespaceReplacementPatterns = array();

    
    protected $includeModifiedDate = true;

    
    protected $includeAuthor = true;

    
    public function __construct(?Composer $composer = null)
    {

        $configExtraSettings = null;

        
        if (isset($composer, $composer->getPackage()->getExtra()['mozart'])) {
            $configExtraSettings = (object)$composer->getPackage()->getExtra()['mozart'];

            
            $this->setDeleteVendorFiles(true);
        }

        if (isset($composer, $composer->getPackage()->getExtra()['strauss'])) {
            $configExtraSettings = (object)$composer->getPackage()->getExtra()['strauss'];
        }

        if (!is_null($configExtraSettings)) {
            $mapper = (new JsonMapperFactory())->bestFit();

            $rename = new Rename();
            $rename->addMapping(StraussConfig::class, 'dep_directory', 'targetDirectory');
            $rename->addMapping(StraussConfig::class, 'dep_namespace', 'namespacePrefix');

            $rename->addMapping(StraussConfig::class, 'exclude_packages', 'excludePackages');
            $rename->addMapping(StraussConfig::class, 'delete_vendor_files', 'deleteVendorFiles');
            $rename->addMapping(StraussConfig::class, 'delete_vendor_packages', 'deleteVendorPackages');

            $rename->addMapping(StraussConfig::class, 'exclude_prefix_packages', 'excludePackagesFromPrefixing');

            $mapper->unshift($rename);
            $mapper->push(new \JsonMapper\Middleware\CaseConversion(
                \JsonMapper\Enums\TextNotation::UNDERSCORE(),
                \JsonMapper\Enums\TextNotation::CAMEL_CASE()
            ));

            $mapper->mapObject($configExtraSettings, $this);
        }

        
        
        
        
        if (! isset($this->namespacePrefix)) {
            if (isset($composer, $composer->getPackage()->getAutoload()['psr-4'])) {
                $this->setNamespacePrefix(array_key_first($composer->getPackage()->getAutoload()['psr-4']));
            } elseif (isset($composer, $composer->getPackage()->getAutoload()['psr-0'])) {
                $this->setNamespacePrefix(array_key_first($composer->getPackage()->getAutoload()['psr-0']));
            } elseif (isset($composer) && '__root__' !== $composer->getPackage()->getName()) {
                $packageName = $composer->getPackage()->getName();
                $namespacePrefix = preg_replace('/[^\w\/]+/', '_', $packageName);
                $namespacePrefix = str_replace('/', '\\', $namespacePrefix) . '\\';
                $namespacePrefix = preg_replace_callback('/(?<=^|_|\\\\)[a-z]/', function ($match) {
                    return strtoupper($match[0]);
                }, $namespacePrefix);
                $this->setNamespacePrefix($namespacePrefix);
            } elseif (isset($this->classmapPrefix)) {
                $namespacePrefix = rtrim($this->getClassmapPrefix(), '_');
                $this->setNamespacePrefix($namespacePrefix);
            }
        }

        if (! isset($this->classmapPrefix)) {
            if (isset($composer, $composer->getPackage()->getAutoload()['psr-4'])) {
                $autoloadKey = array_key_first($composer->getPackage()->getAutoload()['psr-4']);
                $classmapPrefix = str_replace("\\", "_", $autoloadKey);
                $this->setClassmapPrefix($classmapPrefix);
            } elseif (isset($composer, $composer->getPackage()->getAutoload()['psr-0'])) {
                $autoloadKey = array_key_first($composer->getPackage()->getAutoload()['psr-0']);
                $classmapPrefix = str_replace("\\", "_", $autoloadKey);
                $this->setClassmapPrefix($classmapPrefix);
            } elseif (isset($composer) && '__root__' !== $composer->getPackage()->getName()) {
                $packageName = $composer->getPackage()->getName();
                $classmapPrefix = preg_replace('/[^\w\/]+/', '_', $packageName);
                $classmapPrefix = str_replace('/', '\\', $classmapPrefix);
                
                $classmapPrefix = preg_replace_callback('/(?<=^|_|\\\\)[a-z]/', function ($match) {
                    return strtoupper($match[0]);
                }, $classmapPrefix);
                $classmapPrefix = str_replace("\\", "_", $classmapPrefix);
                $this->setClassmapPrefix($classmapPrefix);
            } elseif (isset($this->namespacePrefix)) {
                $classmapPrefix = preg_replace('/[^\w\/]+/', '_', $this->getNamespacePrefix());
                $classmapPrefix = rtrim($classmapPrefix, '_') . '_';
                $this->setClassmapPrefix($classmapPrefix);
            }
        }





        if (isset($composer) && empty($this->packages)) {
            $this->packages = array_map(function (\Composer\Package\Link $element) {
                return $element->getTarget();
            }, $composer->getPackage()->getRequires());
        }

        
        if (!isset($this->classmapOutput)) {
            $this->classmapOutput = true;
            
            if (isset($composer)) {
                foreach ($composer->getPackage()->getAutoload() as $autoload) {
                    
                    foreach ($autoload as $entry) {
                        $paths = (array) $entry;
                        foreach ($paths as $path) {
                            
                            if (trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR === $this->getTargetDirectory()) {
                                $this->classmapOutput = false;
                                break 3;
                            }
                        }
                    }
                }
            }
        }

        
        
        

        if (isset($configExtraSettings, $configExtraSettings->updateCallSites)) {
            if (true === $configExtraSettings->updateCallSites) {
                $this->updateCallSites = null;
            } elseif (false === $configExtraSettings->updateCallSites) {
                $this->updateCallSites = array();
            } elseif (is_array($configExtraSettings->updateCallSites)) {
                $this->updateCallSites = $configExtraSettings->updateCallSites;
            } else {
                
            }
        }
    }

    
    public function getTargetDirectory(): string
    {
        return trim($this->targetDirectory, DIRECTORY_SEPARATOR . '\\/') . DIRECTORY_SEPARATOR;
    }

    
    public function setTargetDirectory(string $targetDirectory): void
    {
        $this->targetDirectory = trim(
            preg_replace(
                '/[\/\\\\]+/',
                DIRECTORY_SEPARATOR,
                $targetDirectory
            ),
            DIRECTORY_SEPARATOR
        )
            . DIRECTORY_SEPARATOR ;
    }

    
    public function getVendorDirectory(): string
    {
        return trim($this->vendorDirectory, DIRECTORY_SEPARATOR . '\\/') . DIRECTORY_SEPARATOR;
    }

    
    public function setVendorDirectory(string $vendorDirectory): void
    {
        $this->vendorDirectory = $vendorDirectory;
    }

    public function getNamespacePrefix(): ?string
    {
        return !isset($this->namespacePrefix) ? null :trim($this->namespacePrefix, '\\');
    }

    
    public function setNamespacePrefix(string $namespacePrefix): void
    {
        $this->namespacePrefix = $namespacePrefix;
    }

    
    public function getClassmapPrefix(): ?string
    {
        return $this->classmapPrefix;
    }

    
    public function setClassmapPrefix(string $classmapPrefix): void
    {
        $this->classmapPrefix = $classmapPrefix;
    }

    
    public function getConstantsPrefix(): ?string
    {
        return $this->constantsPrefix;
    }

    
    public function setConstantsPrefix(string $constantsPrefix): void
    {
        $this->constantsPrefix = $constantsPrefix;
    }

    
    public function getUpdateCallSites(): ?array
    {
        return $this->updateCallSites;
    }

    
    public function setUpdateCallSites(?array $updateCallSites): void
    {
        $this->updateCallSites = $updateCallSites;
    }

    
    public function setExcludeFromCopy(array $excludeFromCopy): void
    {
        foreach (array( 'packages', 'namespaces', 'file_patterns' ) as $key) {
            if (isset($excludeFromCopy[$key])) {
                $this->excludeFromCopy[$key] = $excludeFromCopy[$key];
            }
        }
    }

    
    public function getExcludePackagesFromCopy(): array
    {
        return $this->excludeFromCopy['packages'] ?? array();
    }

    
    public function getExcludeNamespacesFromCopy(): array
    {
        return $this->excludeFromCopy['namespaces'] ?? array();
    }

    
    public function getExcludeFilePatternsFromCopy(): array
    {
        return $this->excludeFromCopy['file_patterns'] ?? array();
    }

    
    public function setExcludeFromPrefix(array $excludeFromPrefix): void
    {
        if (isset($excludeFromPrefix['packages'])) {
            $this->excludeFromPrefix['packages'] = $excludeFromPrefix['packages'];
        }
        if (isset($excludeFromPrefix['namespaces'])) {
            $this->excludeFromPrefix['namespaces'] = $excludeFromPrefix['namespaces'];
        }
        if (isset($excludeFromPrefix['file_patterns'])) {
            $this->excludeFromPrefix['file_patterns'] = $excludeFromPrefix['file_patterns'];
        }
    }

    
    public function getExcludePackagesFromPrefixing(): array
    {
        return $this->excludeFromPrefix['packages'] ?? array();
    }

    
    public function setExcludePackagesFromPrefixing(array $excludePackagesFromPrefixing): void
    {
        $this->excludeFromPrefix['packages'] = $excludePackagesFromPrefixing;
    }

    
    public function getExcludeNamespacesFromPrefixing(): array
    {
        return $this->excludeFromPrefix['namespaces'] ?? array();
    }

    
    public function getExcludeFilePatternsFromPrefixing(): array
    {
        return $this->excludeFromPrefix['file_patterns'] ?? array();
    }


    
    public function getOverrideAutoload(): array
    {
        return $this->overrideAutoload;
    }

    
    public function setOverrideAutoload(array $overrideAutoload): void
    {
        $this->overrideAutoload = $overrideAutoload;
    }

    
    public function isDeleteVendorFiles(): bool
    {
        return $this->deleteVendorFiles;
    }

    
    public function isDeleteVendorPackages(): bool
    {
        return $this->deleteVendorPackages;
    }

    
    public function setDeleteVendorFiles(bool $deleteVendorFiles): void
    {
        $this->deleteVendorFiles = $deleteVendorFiles;
    }

    
    public function setDeleteVendorPackages(bool $deleteVendorPackages): void
    {
        $this->deleteVendorPackages = $deleteVendorPackages;
    }

    
    public function getPackages(): array
    {
        return $this->packages;
    }

    
    public function setPackages(array $packages): void
    {
        $this->packages = $packages;
    }

    
    public function isClassmapOutput(): bool
    {
        return $this->classmapOutput;
    }

    
    public function setClassmapOutput(bool $classmapOutput): void
    {
        $this->classmapOutput = $classmapOutput;
    }

    
    public function setExcludePackages(array $excludePackages): void
    {
        $this->excludeFromPrefix['packages'] = $excludePackages;
    }

    
    public function getNamespaceReplacementPatterns(): array
    {
        return $this->namespaceReplacementPatterns;
    }

    
    public function setNamespaceReplacementPatterns(array $namespaceReplacementPatterns): void
    {
        $this->namespaceReplacementPatterns = $namespaceReplacementPatterns;
    }

    
    public function isIncludeModifiedDate(): bool
    {
        return $this->includeModifiedDate;
    }

    
    public function setIncludeModifiedDate(bool $includeModifiedDate): void
    {
        $this->includeModifiedDate = $includeModifiedDate;
    }


    
    public function isIncludeAuthor(): bool
    {
        return $this->includeAuthor;
    }

    
    public function setIncludeAuthor(bool $includeAuthor): void
    {
        $this->includeAuthor = $includeAuthor;
    }

    
    public function updateFromCli(InputInterface $input): void
    {

        
        
        

        if ($input->hasOption('updateCallSites') && $input->getOption('updateCallSites') !== null) {
            $updateCallSitesInput = $input->getOption('updateCallSites');

            if ('false' === $updateCallSitesInput) {
                $this->updateCallSites = array();
            } elseif ('true' === $updateCallSitesInput) {
                $this->updateCallSites = null;
            } elseif (! is_null($updateCallSitesInput)) {
                $this->updateCallSites = explode(',', $updateCallSitesInput);
            }
        }

        if ($input->hasOption('deleteVendorPackages') && $input->getOption('deleteVendorPackages') !== null) {
            $isDeleteVendorPackagesCommandLine = $input->getOption('deleteVendorPackages') === 'true';
            $this->setDeleteVendorPackages($isDeleteVendorPackagesCommandLine);
        } elseif ($input->hasOption('delete_vendor_packages') && $input->getOption('delete_vendor_packages') !== null) {
            $isDeleteVendorPackagesCommandLine = $input->getOption('delete_vendor_packages') === 'true';
            $this->setDeleteVendorPackages($isDeleteVendorPackagesCommandLine);
        }
    }
}
