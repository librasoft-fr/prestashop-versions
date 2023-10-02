<?php

namespace PrestaShop\Autoload;

use Closure;

final class Autoloader
{
    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @var array<int|string, array<string, string|null>>
     */
    private $classIndex = [];

    /**
     * Must end with \\ because some classes exists with a namespace begining by `Entity`.
     * ex with class PrestaShop\PrestaShop\Adapter\EntityTranslation\DataLangFactory;.
     */
    private const NAMESPACED_CLASSES = 'PrestaShop\\PrestaShop\\Adapter\\Entity\\';

    /**
     * @var string[]
     */
    private static $class_aliases = [
        'Collection' => 'PrestaShopCollection',
        'Autoload' => 'PrestaShopAutoload',
        'Backup' => 'PrestaShopBackup',
        'Logger' => 'PrestaShopLogger',
    ];

    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * @var array<string, bool>
     */
    private $loadedClasses = [];

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var Closure|null
     */
    private $initializationCallback;

    public function __construct(string $directory)
    {
        if (!str_ends_with($directory, DIRECTORY_SEPARATOR)) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        $this->rootDirectory = $directory;
    }

    public function register(): void
    {
        if ($this->registered) {
            throw new \RuntimeException('Autoload is already registered.');
        }

        spl_autoload_register([$this, 'load']);
        $this->registered = true;
    }

    public function load(string $className): void
    {
        if (!$this->initialized && null !== $this->initializationCallback) {
            ($this->initializationCallback)();
            $this->initialized = true;
        }

        if (str_starts_with($className, self::NAMESPACED_CLASSES)) {
            $classWithoutNs = substr($className, strlen(self::NAMESPACED_CLASSES));
            class_alias($classWithoutNs, '\\'.$className);

            return;
        }

        if (array_key_exists($className, $this->loadedClasses)) {
            return;
        }

        if (array_key_exists($className, self::$class_aliases)) {
            $this->loadClassAlias($className);
        }

        // Class is not handled is this autoload
        if (!array_key_exists($className, $this->classIndex)) {
            return;
        }

        // Try to load a class that is in classIndex and has a specified path (ex: core classes, interfaces)
        if (null !== $this->classIndex[$className]['path']) {
            require_once $this->rootDirectory.$this->classIndex[$className]['path'];
            $this->loadedClasses[$className] = true;

            return;
        }

        $coreClass = $className.LegacyClassLoader::CORE_SUFFIX;
        if (array_key_exists($coreClass, $this->classIndex) && $this->isClass((string) $this->classIndex[$coreClass]['type'])) {
            // cannot use class_alias because get_class() returns the Core class name
            // @see https://www.php.net/manual/en/function.class-alias.php#101636
            eval($this->classIndex[$coreClass]['type'].' '.$className.' extends '.$coreClass.' {}');
        }

        $this->loadedClasses[$className] = true;
    }

    /**
     * @param array<int|string, array<string, string|null>> $classIndex
     */
    public function setClassIndex(array $classIndex): void
    {
        $this->classIndex = $classIndex;
    }

    public function getClassPath(string $className): ?string
    {
        return $this->classIndex[$className]['path'] ?? null;
    }

    private function isClass(string $type): bool
    {
        return LegacyClassLoader::TYPE_CLASS === $type || LegacyClassLoader::TYPE_ABSTRACT_CLASS === $type;
    }

    private function loadClassAlias(string $className): void
    {
        eval('class '.$className.' extends '.self::$class_aliases[$className].' {}');
        $this->loadedClasses[$className] = true;
    }

    public function setInitializationCallBack(Closure $closure): self
    {
        $this->initializationCallback = $closure;

        return $this;
    }
}
