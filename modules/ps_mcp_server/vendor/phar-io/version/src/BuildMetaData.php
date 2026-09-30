<?php declare(strict_types = 1);

namespace PharIo\Version;

class BuildMetaData {

    
    private $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    public function asString(): string {
        return $this->value;
    }

    public function equals(BuildMetaData $other): bool {
        return $this->asString() === $other->asString();
    }
}
