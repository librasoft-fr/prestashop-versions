<?php declare(strict_types = 1);

namespace PharIo\Version;

class GreaterThanOrEqualToVersionConstraint extends AbstractVersionConstraint {
    
    private $minimalVersion;

    public function __construct(string $originalValue, Version $minimalVersion) {
        parent::__construct($originalValue);

        $this->minimalVersion = $minimalVersion;
    }

    public function complies(Version $version): bool {
        return $version->getVersionString() === $this->minimalVersion->getVersionString()
            || $version->isGreaterThan($this->minimalVersion);
    }
}
