<?php

namespace Prestashop\ModuleLibMboInstaller;

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class Installer
{
    const ADDONS_URL = 'https://api-addons.prestashop.com';
    const MODULE_ID = 39574;
    const MODULE_NAME = 'ps_mbo';

    
    protected $marketplaceClient;

    
    protected $moduleManager;

    
    protected $prestashopVersion;

    
    public function __construct($prestashopVersion)
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        if (is_null($moduleManagerBuilder)) {
            throw new \Exception('ModuleManagerBuilder::getInstance() failed');
        }

        $this->moduleManager = $moduleManagerBuilder->build();
        if (is_null($this->moduleManager)) {
            throw new \Exception('ModuleManagerBuilder::build() failed');
        }

        $this->marketplaceClient = new HttpClient(self::ADDONS_URL);
        $this->prestashopVersion = $prestashopVersion;
    }

    
    public function installModule()
    {
        
        
        if (version_compare($this->prestashopVersion, '8.0.0', '>=')) {
            return $this->moduleManager->install(self::MODULE_NAME, $this->downloadModule());
        }

        return $this->moduleManager->install(self::MODULE_NAME);
    }

    
    public function enableModule()
    {
        return $this->moduleManager->enable(self::MODULE_NAME);
    }

    
    private function downloadModule()
    {
        $params = [
            'id_module' => self::MODULE_ID,
            'channel' => 'stable',
            'method' => 'module',
            'version' => $this->prestashopVersion,
        ];

        $fetchModuleData = $this->marketplaceClient->post('/?', $params);
        $moduleData = $fetchModuleData->getBody();

        if (!$fetchModuleData->isSuccessful()) {
            throw new \Exception('An error occured while fetching data');
        }

        $temporaryZipFilename = tempnam(sys_get_temp_dir(), 'mod');
        if ($temporaryZipFilename === false) {
            throw new \Exception('Cannot create temporary file in ' . sys_get_temp_dir());
        }

        if (file_put_contents($temporaryZipFilename, $moduleData) !== false) {
            return $temporaryZipFilename;
        } else {
            throw new \Exception('Cannot store module content in temporary file !');
        }
    }
}
