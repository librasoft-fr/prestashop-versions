<?php

namespace PrestaShop\Autoload;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Parses all the core classes to build indexes file used by the Prestashop Autoload.
 */
final class LegacyClassLoader
{
    public const CORE_SUFFIX = 'Core';

    /**
     * @var string[]
     */
    private $coreDirectories = ['classes', 'controllers'];

    /**
     * @var string[]
     */
    private $overrideDirectories = ['override'];

    /**
     * @var Filesystem
     */
    private $filesystem;

    public const TYPE_CLASS = 'class';
    public const TYPE_ABSTRACT_CLASS = 'abstract class';
    public const TYPE_INTERFACE = 'interface';

    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * @var string
     */
    private $cacheDirectory;

    public function __construct(string $directory, string $cacheDirectory)
    {
        if (!str_ends_with($directory, DIRECTORY_SEPARATOR)) {
            $directory .= DIRECTORY_SEPARATOR;
        }
        $this->rootDirectory = $directory;
        $this->cacheDirectory = $cacheDirectory;
        $this->filesystem = new Filesystem();
    }

    /**
     * @return array<int|string, array<string, string|null>>
     */
    public function buildClassIndex(bool $enableOverrides): array
    {
        $this->filesystem->mkdir($this->cacheDirectory);

        $classes = $this->parseClasses(
            $this->loadClasses($this->coreDirectories)
        );

        if ($enableOverrides) {
            $overrideClasses = $this->parseClasses(
                $this->loadClasses($this->overrideDirectories)
            );

            $classes = array_merge($classes, $overrideClasses);
        }

        ksort($classes);
        $this->dumpClassIndex($classes);

        return $classes;
    }

    /**
     * Get Class index cache file.
     */
    public function getClassIndexFilepath(): string
    {
        return $this->cacheDirectory.'class_index.php';
    }

    /**
     * @param array<string> $directories
     *
     * @return array<string, SplFileInfo>
     */
    private function loadClasses(array $directories): array
    {
        // The finder cannot loop on directories that does not exist.
        // So we must check dirs before putting them in the finder
        $directories = array_filter(array_map(function ($value) {
            return $this->rootDirectory.$value;
        }, $directories), static function ($directory) {
            return is_dir($directory);
        });

        if ([] === $directories) {
            return [];
        }

        $finder = new Finder();
        $finder
            ->in($directories)
            ->followLinks()
            ->files()
            ->name('*.php')
        ;

        return iterator_to_array($finder->getIterator());
    }

    /**
     * @param SplFileInfo[] $files
     *
     * @return array<int|string, array<string, string|null>>
     */
    private function parseClasses(array $files): array
    {
        $coreSuffixLength = strlen(self::CORE_SUFFIX);
        $classes = [];
        $rootDirStrLen = strlen($this->rootDirectory);
        foreach ($files as $file) {
            $content = $file->getContents();

            $namePattern = '[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*';
            $nameWithNsPattern = '(?:\\\\?(?:'.$namePattern.'\\\\)*'.$namePattern.')';
            $pattern = '~(?<!\w)((abstract\s+)?class|interface)\s+(?P<classname>'.$file->getBasename('.php').'(?:'.self::CORE_SUFFIX.')?)'
                .'(?:\s+extends\s+'.$nameWithNsPattern.')?(?:\s+implements\s+'.$nameWithNsPattern.'(?:\s*,\s*'.$nameWithNsPattern.')*)?\s*\{~i';

            if (preg_match($pattern, $content, $m)) {
                $classes[$m['classname']] = [
                    'path' => (string) substr($file->getPathname(), $rootDirStrLen),
                    'type' => trim($m[1]),
                ];
                if (str_ends_with($m['classname'], self::CORE_SUFFIX)) {
                    $classes[(string) substr($m['classname'], 0, -$coreSuffixLength)] = [
                        'path' => null,
                        'type' => $classes[$m['classname']]['type'],
                    ];
                }
            }
        }

        return $classes;
    }

    /**
     * @param array<int|string, array<string, string|null>> $classes
     */
    private function dumpClassIndex(array $classes): void
    {
        $content = '<?php return '.var_export($classes, true).'; ?>';

        $this->filesystem->dumpFile($this->getClassIndexFilepath(), $content);
    }

    public function getCacheDirectory(): string
    {
        return $this->cacheDirectory;
    }

    /**
     * @return array<int|string, array<string, string|null>>
     */
    public function loadClassCache(): array
    {
        $cacheFile = $this->getClassIndexFilepath();
        if (is_file($cacheFile) && is_readable($cacheFile)) {
            return include $cacheFile;
        }

        throw new \RuntimeException(__CLASS__.' has no cache file');
    }
}
