<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use function sprintf;

class Author {
    
    private $name;

    
    private $email;

    public function __construct(string $name, ?Email $email = null) {
        $this->name  = $name;
        $this->email = $email;
    }

    public function asString(): string {
        if (!$this->hasEmail()) {
            return $this->name;
        }

        return sprintf(
            '%s <%s>',
            $this->name,
            $this->email->asString()
        );
    }

    public function getName(): string {
        return $this->name;
    }

    
    public function hasEmail(): bool {
        return $this->email !== null;
    }

    public function getEmail(): Email {
        if (!$this->hasEmail()) {
            throw new NoEmailAddressException();
        }

        return $this->email;
    }
}
