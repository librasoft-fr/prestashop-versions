<?php declare(strict_types = 1);

namespace PharIo\Version;

class ExactVersionConstraint extends AbstractVersionConstraint {
    public function complies(Version $version): bool {
        $other = $version->getVersionString();

        if ($version->hasBuildMetaData()) {
            $other .= '+' . $version->getBuildMetaData()->asString();
        }

        return $this->asString() === $other;
    }
}
