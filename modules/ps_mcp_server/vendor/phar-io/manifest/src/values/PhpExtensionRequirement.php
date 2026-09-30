<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class PhpExtensionRequirement implements Requirement {
    
    private $extension;

    public function __construct(string $extension) {
        $this->extension = $extension;
    }

    public function asString(): string {
        return $this->extension;
    }
}
