<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class ExtElement extends ManifestElement {
    public function getName(): string {
        return $this->getAttributeValue('name');
    }
}
