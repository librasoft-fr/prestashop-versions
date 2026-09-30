<?php declare(strict_types = 1);

namespace PharIo\Version;

class SpecificMajorVersionConstraint extends AbstractVersionConstraint {
    
    private $major;

    public function __construct(string $originalValue, int $major) {
        parent::__construct($originalValue);

        $this->major = $major;
    }

    public function complies(Version $version): bool {
        return $version->getMajor()->getValue() === $this->major;
    }
}
