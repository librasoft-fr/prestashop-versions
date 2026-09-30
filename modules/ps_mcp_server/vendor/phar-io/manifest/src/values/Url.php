<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use const FILTER_VALIDATE_URL;
use function filter_var;

class Url {
    
    private $url;

    public function __construct(string $url) {
        $this->ensureUrlIsValid($url);

        $this->url = $url;
    }

    public function asString(): string {
        return $this->url;
    }

    
    private function ensureUrlIsValid(string $url): void {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidUrlException;
        }
    }
}
