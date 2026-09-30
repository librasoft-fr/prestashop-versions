<?php declare(strict_types = 1);

namespace PharIo\Version;

interface VersionConstraint {
    public function complies(Version $version): bool;

    public function asString(): string;
}
