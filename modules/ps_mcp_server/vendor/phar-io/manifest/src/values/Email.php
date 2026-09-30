<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use const FILTER_VALIDATE_EMAIL;
use function filter_var;

class Email {
    
    private $email;

    public function __construct(string $email) {
        $this->ensureEmailIsValid($email);

        $this->email = $email;
    }

    public function asString(): string {
        return $this->email;
    }

    private function ensureEmailIsValid(string $url): void {
        if (filter_var($url, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmailException;
        }
    }
}
