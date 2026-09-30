<?php declare(strict_types = 1);

namespace PharIo\Manifest;

class BundlesElement extends ManifestElement {
    public function getComponentElements(): ComponentElementCollection {
        return new ComponentElementCollection(
            $this->getChildrenByName('component')
        );
    }
}
