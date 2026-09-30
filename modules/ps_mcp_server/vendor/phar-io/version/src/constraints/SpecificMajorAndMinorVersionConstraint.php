<?php declare(strict_types = 1);

namespace PharIo\Version;

class SpecificMajorAndMinorVersionConstraint extends AbstractVersionConstraint {
    
    private $major;

    
    private $minor;

    public function __construct(string $originalValue, int $major, int $minor) {
        parent::__construct($originalValue);

        $this->major = $major;
        $this->minor = $minor;
    }

    public function complies(Version $version): bool {
        if ($version->getMajor()->getValue() !== $this->major) {
            return false;
        }

        return $version->getMinor()->getValue() === $this->minor;
    }
}
