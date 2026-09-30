<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use PharIo\Version\Version;

class BundledComponent {
    
    private $name;

    
    private $version;

    public function __construct(string $name, Version $version) {
        $this->name    = $name;
        $this->version = $version;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getVersion(): Version {
        return $this->version;
    }
}
