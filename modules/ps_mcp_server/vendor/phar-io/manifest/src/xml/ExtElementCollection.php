<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class ExtElementCollection extends ElementCollection {
    public function current(): ExtElement {
        return new ExtElement(
            $this->getCurrentElement()
        );
    }
}
