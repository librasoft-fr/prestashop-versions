<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class Library extends Type {
    public function isLibrary(): bool {
        return true;
    }
}
