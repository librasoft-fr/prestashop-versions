<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class RequiresElement extends ManifestElement {
    public function getPHPElement(): PhpElement {
        return new PhpElement(
            $this->getChildByName('php')
        );
    }
}
