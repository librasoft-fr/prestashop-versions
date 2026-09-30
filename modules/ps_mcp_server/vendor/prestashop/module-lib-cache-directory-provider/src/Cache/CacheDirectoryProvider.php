<?php


namespace PrestaShop\ModuleLibCacheDirectoryProvider\Cache;


class CacheDirectoryProvider
{
    
    private $psVersion;

    
    private $psPath;

    
    private $psIsDebugMode;

    
    public function __construct($psVersion, $psPath, $psIsDebugMode)
    {
        $this->psVersion = $psVersion;
        $this->psPath = $psPath;
        $this->psIsDebugMode = $psIsDebugMode;
    }

    
    public function getPath()
    {
        if (defined('_PS_CACHE_DIR_')) {
            return constant('_PS_CACHE_DIR_');
        }

        $path = '/var/cache/' . $this->getEnvName();

        if (version_compare($this->psVersion, '1.7.0.0', '<')) {
            $path = '/cache';
        } elseif (version_compare($this->psVersion, '1.7.4.0', '<')) {
            $path = '/app/cache/' . $this->getEnvName();
        }

        return $this->psPath . $path;
    }

    
    public function isWritable()
    {
        return is_writable($this->getPath());
    }

    
    public function isReadable()
    {
        return is_readable($this->getPath());
    }

    
    private function getEnvName()
    {
        return $this->psIsDebugMode ? 'dev' : 'prod';
    }
}
