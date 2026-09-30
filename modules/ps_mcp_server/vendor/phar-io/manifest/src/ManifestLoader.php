<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use function sprintf;

class ManifestLoader {
    public static function fromFile(string $filename): Manifest {
        try {
            return (new ManifestDocumentMapper())->map(
                ManifestDocument::fromFile($filename)
            );
        } catch (Exception $e) {
            throw new ManifestLoaderException(
                sprintf('Loading %s failed.', $filename),
                (int)$e->getCode(),
                $e
            );
        }
    }

    public static function fromPhar(string $filename): Manifest {
        return self::fromFile('phar://' . $filename . '/manifest.xml');
    }

    public static function fromString(string $manifest): Manifest {
        try {
            return (new ManifestDocumentMapper())->map(
                ManifestDocument::fromString($manifest)
            );
        } catch (Exception $e) {
            throw new ManifestLoaderException(
                'Processing string failed',
                (int)$e->getCode(),
                $e
            );
        }
    }
}
