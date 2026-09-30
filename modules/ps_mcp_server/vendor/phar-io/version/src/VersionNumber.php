<?php declare(strict_types = 1);

namespace PharIo\Version;

class VersionNumber {

    
    private $value;

    public function __construct(?int $value) {
        $this->value = $value;
    }

    public function isAny(): bool {
        return $this->value === null;
    }

    public function getValue(): ?int {
        return $this->value;
    }
}
