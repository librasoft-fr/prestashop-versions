<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class LicenseElement extends ManifestElement {
    public function getType(): string {
        return $this->getAttributeValue('type');
    }

    public function getUrl(): string {
        return $this->getAttributeValue('url');
    }
}
