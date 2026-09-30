<?php


namespace PrestaShop\ModuleLibServiceContainer\DependencyInjection;

use PrestaShop\ModuleLibCacheDirectoryProvider\Cache\CacheDirectoryProvider;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerProvider
{
    
    private $moduleName;

    
    private $moduleLocalPath;

    
    private $cacheDirectory;

    
    public function __construct($moduleName, $moduleLocalPath, CacheDirectoryProvider $cacheDirectory)
    {
        $this->moduleName = $moduleName;
        $this->moduleLocalPath = $moduleLocalPath;
        $this->cacheDirectory = $cacheDirectory;
    }

    
    public function get($containerName)
    {
        $containerClassName = ucfirst($this->moduleName)
            . ucfirst($containerName)
            . 'Container'
        ;
        $containerFilePath = $this->cacheDirectory->getPath() . '/' . $containerClassName . '.php';
        $containerConfigCache = new ConfigCache($containerFilePath, constant('_PS_MODE_DEV_'));

        if ($containerConfigCache->isFresh()) {
            require_once $containerFilePath;

            return new $containerClassName();
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->set(
            $this->moduleName . '.cache.directory',
            $this->cacheDirectory
        );
        $moduleConfigPath = $this->moduleLocalPath
            . 'config/'
            . $containerName
        ;
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($moduleConfigPath));
        $loader->load('services.yml');
        $containerBuilder->compile();
        $dumper = new PhpDumper($containerBuilder);
        $containerConfigCache->write(
            $dumper->dump(['class' => $containerClassName]),
            $containerBuilder->getResources()
        );

        return $containerBuilder;
    }
}
