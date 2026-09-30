<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class CopyrightElement extends ManifestElement {
    public function getAuthorElements(): AuthorElementCollection {
        return new AuthorElementCollection(
            $this->getChildrenByName('author')
        );
    }

    public function getLicenseElement(): LicenseElement {
        return new LicenseElement(
            $this->getChildByName('license')
        );
    }
}
