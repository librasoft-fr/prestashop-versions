<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class ExtensionElement extends ManifestElement {
    public function getFor(): string {
        return $this->getAttributeValue('for');
    }

    public function getCompatible(): string {
        return $this->getAttributeValue('compatible');
    }
}
