<?php declare(strict_types = 1);

namespace PharIo\Version;

class AnyVersionConstraint implements VersionConstraint {
    public function complies(Version $version): bool {
        return true;
    }

    public function asString(): string {
        return '*';
    }
}
