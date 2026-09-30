<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use PharIo\Version\Version;

class Manifest {
    
    private $name;

    
    private $version;

    
    private $type;

    
    private $copyrightInformation;

    
    private $requirements;

    
    private $bundledComponents;

    public function __construct(ApplicationName $name, Version $version, Type $type, CopyrightInformation $copyrightInformation, RequirementCollection $requirements, BundledComponentCollection $bundledComponents) {
        $this->name                 = $name;
        $this->version              = $version;
        $this->type                 = $type;
        $this->copyrightInformation = $copyrightInformation;
        $this->requirements         = $requirements;
        $this->bundledComponents    = $bundledComponents;
    }

    public function getName(): ApplicationName {
        return $this->name;
    }

    public function getVersion(): Version {
        return $this->version;
    }

    public function getType(): Type {
        return $this->type;
    }

    public function getCopyrightInformation(): CopyrightInformation {
        return $this->copyrightInformation;
    }

    public function getRequirements(): RequirementCollection {
        return $this->requirements;
    }

    public function getBundledComponents(): BundledComponentCollection {
        return $this->bundledComponents;
    }

    public function isApplication(): bool {
        return $this->type->isApplication();
    }

    public function isLibrary(): bool {
        return $this->type->isLibrary();
    }

    public function isExtension(): bool {
        return $this->type->isExtension();
    }

    public function isExtensionFor(ApplicationName $application, ?Version $version = null): bool {
        if (!$this->isExtension()) {
            return false;
        }

        
        $type = $this->type;

        if ($version !== null) {
            return $type->isCompatibleWith($application, $version);
        }

        return $type->isExtensionFor($application);
    }
}
