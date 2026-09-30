<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class ComponentElement extends ManifestElement {
    public function getName(): string {
        return $this->getAttributeValue('name');
    }

    public function getVersion(): string {
        return $this->getAttributeValue('version');
    }
}
