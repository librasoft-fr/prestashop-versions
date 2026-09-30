<?php declare(strict_types=1);



namespace Composer\Package\Archiver;

use PharData;


class PharArchiver implements ArchiverInterface
{
    
    protected static $formats = [
        'zip' => \Phar::ZIP,
        'tar' => \Phar::TAR,
        'tar.gz' => \Phar::TAR,
        'tar.bz2' => \Phar::TAR,
    ];

    
    protected static $compressFormats = [
        'tar.gz' => \Phar::GZ,
        'tar.bz2' => \Phar::BZ2,
    ];

    
    public function archive(string $sources, string $target, string $format, array $excludes = [], bool $ignoreFilters = false): string
    {
        $sources = realpath($sources);

        
        if (file_exists($target)) {
            unlink($target);
        }

        try {
            $filename = substr($target, 0, strrpos($target, $format) - 1);

            
            if (isset(static::$compressFormats[$format])) {
                
                $target = $filename . '.tar';
            }

            $phar = new PharData(
                $target,
                \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO,
                '',
                static::$formats[$format]
            );
            $files = new ArchivableFilesFinder($sources, $excludes, $ignoreFilters);
            $filesOnly = new ArchivableFilesFilter($files);
            $phar->buildFromIterator($filesOnly, $sources);
            $filesOnly->addEmptyDir($phar, $sources);

            if (!file_exists($target)) {
                $target = $filename . '.' . $format;
                unset($phar);

                if ($format === 'tar') {
                    
                    file_put_contents($target, str_repeat("\0", 10240));
                } elseif ($format === 'zip') {
                    
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
                } elseif ($format === 'tar.gz' || $format === 'tar.bz2') {
                    if (!PharData::canCompress(static::$compressFormats[$format])) {
                        throw new \RuntimeException(sprintf('Can not compress to %s format', $format));
                    }
                    if ($format === 'tar.gz' && function_exists('gzcompress')) {
                        file_put_contents($target, gzcompress(str_repeat("\0", 10240)));
                    } elseif ($format === 'tar.bz2' && function_exists('bzcompress')) {
                        file_put_contents($target, bzcompress(str_repeat("\0", 10240)));
                    }
                }

                return $target;
            }

            if (isset(static::$compressFormats[$format])) {
                
                if (!PharData::canCompress(static::$compressFormats[$format])) {
                    throw new \RuntimeException(sprintf('Can not compress to %s format', $format));
                }

                
                unlink($target);

                
                $phar->compress(static::$compressFormats[$format]);

                
                $target = $filename . '.' . $format;
            }

            return $target;
        } catch (\UnexpectedValueException $e) {
            $message = sprintf(
                "Could not create archive '%s' from '%s': %s",
                $target,
                $sources,
                $e->getMessage()
            );

            throw new \RuntimeException($message, $e->getCode(), $e);
        }
    }

    
    public function supports(string $format, ?string $sourceType): bool
    {
        return isset(static::$formats[$format]);
    }
}
