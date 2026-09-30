<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class PhpElement extends ManifestElement {
    public function getVersion(): string {
        return $this->getAttributeValue('version');
    }

    public function hasExtElements(): bool {
        return $this->hasChild('ext');
    }

    public function getExtElements(): ExtElementCollection {
        return new ExtElementCollection(
            $this->getChildrenByName('ext')
        );
    }
}
