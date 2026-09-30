<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class Application extends Type {
    public function isApplication(): bool {
        return true;
    }
}
