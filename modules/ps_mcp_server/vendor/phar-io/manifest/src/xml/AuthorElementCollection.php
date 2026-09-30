<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class AuthorElementCollection extends ElementCollection {
    public function current(): AuthorElement {
        return new AuthorElement(
            $this->getCurrentElement()
        );
    }
}
