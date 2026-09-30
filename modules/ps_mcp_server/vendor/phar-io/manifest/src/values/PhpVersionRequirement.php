<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use PharIo\Version\VersionConstraint;

class PhpVersionRequirement implements Requirement {
    
    private $versionConstraint;

    public function __construct(VersionConstraint $versionConstraint) {
        $this->versionConstraint = $versionConstraint;
    }

    public function getVersionConstraint(): VersionConstraint {
        return $this->versionConstraint;
    }
}
