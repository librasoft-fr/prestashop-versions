<?php declare(strict_types = 1);

namespace PharIo\Version;

abstract class AbstractVersionConstraint implements VersionConstraint {
    
    private $originalValue;

    public function __construct(string $originalValue) {
        $this->originalValue = $originalValue;
    }

    public function asString(): string {
        return $this->originalValue;
    }
}
