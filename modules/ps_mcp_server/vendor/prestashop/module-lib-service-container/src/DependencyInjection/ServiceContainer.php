<?php


namespace PrestaShop\ModuleLibServiceContainer\DependencyInjection;

use PrestaShop\ModuleLibCacheDirectoryProvider\Cache\CacheDirectoryProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceContainer
{
    
    private $moduleName;

    
    private $moduleLocalPath;

    
    private $container;

    
    public function __construct($moduleName, $moduleLocalPath)
    {
        $this->moduleName = $moduleName;
        $this->moduleLocalPath = $moduleLocalPath;
    }

    
    public function getService($serviceName)
    {
        if (null === $this->container) {
            $this->initContainer();
        }

        return $this->container->get($serviceName);
    }

    
    private function initContainer()
    {
        $cacheDirectory = new CacheDirectoryProvider(
            constant('_PS_VERSION_'),
            constant('_PS_ROOT_DIR_'),
            constant('_PS_MODE_DEV_')
        );
        $containerProvider = new ContainerProvider($this->moduleName, $this->moduleLocalPath, $cacheDirectory);

        $this->container = $containerProvider->get(defined('_PS_ADMIN_DIR_') || defined('PS_INSTALLATION_IN_PROGRESS') || PHP_SAPI === 'cli' ? 'admin' : 'front');
    }
}
