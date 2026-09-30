<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class ComponentElementCollection extends ElementCollection {
    public function current(): ComponentElement {
        return new ComponentElement(
            $this->getCurrentElement()
        );
    }
}
