<?php declare(strict_types=1);



namespace Composer\Package\Archiver;

use Composer\Util\Filesystem;
use Composer\Util\Platform;
use ZipArchive;


class ZipArchiver implements ArchiverInterface
{
    
    protected static $formats = [
        'zip' => true,
    ];

    
    public function archive(string $sources, string $target, string $format, array $excludes = [], bool $ignoreFilters = false): string
    {
        $fs = new Filesystem();
        $sourcesRealpath = realpath($sources);
        if (false !== $sourcesRealpath) {
            $sources = $sourcesRealpath;
        }
        unset($sourcesRealpath);
        $sources = $fs->normalizePath($sources);

        $zip = new ZipArchive();
        $res = $zip->open($target, ZipArchive::CREATE);
        if ($res === true) {
            $files = new ArchivableFilesFinder($sources, $excludes, $ignoreFilters);
            foreach ($files as $file) {
                
                $filepath = $file->getPathname();
                $relativePath = $file->getRelativePathname();

                if (Platform::isWindows()) {
                    $relativePath = strtr($relativePath, '\\', '/');
                }

                if ($file->isDir()) {
                    $zip->addEmptyDir($relativePath);
                } else {
                    $zip->addFile($filepath, $relativePath);
                }

                
                if (method_exists($zip, 'setExternalAttributesName')) {
                    $perms = fileperms($filepath);

                    
                    $zip->setExternalAttributesName($relativePath, ZipArchive::OPSYS_UNIX, $perms << 16);
                }
            }
            if ($zip->close()) {
                if (!file_exists($target)) {
                    
                    $eocd = pack(
                        'VvvvvVVv',
                        0x06054b50,  
                        0,           
                        0,           
                        0,           
                        0,           
                        0,           
                        0,           
                        0            
                    );
                    file_put_contents($target, $eocd);
                }

                return $target;
            }
        }
        $message = sprintf(
            "Could not create archive '%s' from '%s': %s",
            $target,
            $sources,
            $zip->getStatusString()
        );
        throw new \RuntimeException($message);
    }

    
    public function supports(string $format, ?string $sourceType): bool
    {
        return isset(static::$formats[$format]) && $this->compressionAvailable();
    }

    private function compressionAvailable(): bool
    {
        return class_exists('ZipArchive');
    }
}
