<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class AuthorElement extends ManifestElement {
    public function getName(): string {
        return $this->getAttributeValue('name');
    }

    public function getEmail(): string {
        return $this->getAttributeValue('email');
    }

    public function hasEMail(): bool {
        return $this->hasAttribute('email');
    }
}
