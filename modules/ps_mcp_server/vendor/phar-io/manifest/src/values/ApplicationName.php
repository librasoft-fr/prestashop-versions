<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use function preg_match;
use function sprintf;

class ApplicationName {
    
    private $name;

    public function __construct(string $name) {
        $this->ensureValidFormat($name);
        $this->name = $name;
    }

    public function asString(): string {
        return $this->name;
    }

    public function isEqual(ApplicationName $name): bool {
        return $this->name === $name->name;
    }

    private function ensureValidFormat(string $name): void {
        if (!preg_match('#\w/\w#', $name)) {
            throw new InvalidApplicationNameException(
                sprintf('Format of name "%s" is not valid - expected: vendor/packagename', $name),
                InvalidApplicationNameException::InvalidFormat
            );
        }
    }
}
