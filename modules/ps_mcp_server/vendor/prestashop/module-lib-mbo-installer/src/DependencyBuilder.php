<?php

namespace Prestashop\ModuleLibMboInstaller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class DependencyBuilder
{
    const DEPENDENCY_FILENAME = 'module_dependencies.json';
    const GET_PARAMETER = 'mbo_action_needed';

    
    protected $module;

    
    protected $router;

    
    public function __construct($module)
    {
        $this->module = $module;
        $this->buildRouter();
    }

    
    public function handleDependencies()
    {
        $this->handleMboInstallation();

        return $this->buildDependenciesContext();
    }

    
    public function areDependenciesMet()
    {
        $dependencies = $this->getDependencies(false);

        foreach ($dependencies as $dependencyName => $dependency) {
            if (
                !array_key_exists('installed', $dependency)
                || !array_key_exists('enabled', $dependency)
                || false === $dependency['installed']
                || false === $dependency['enabled']
            ) {
                return false;
            }
        }

        return true;
    }

    
    protected function handleMboInstallation()
    {
        if (!isset($_GET[self::GET_PARAMETER]) || !$this->isMboNeeded()) {
            return;
        }

        $mboStatus = (new Presenter())->present();
        $installer = new Installer(_PS_VERSION_);

        if ($mboStatus['isInstalled'] && $mboStatus['isEnabled']) {
            return;
        }

        $data = [Installer::MODULE_NAME => [
            'status' => true,
        ]];

        try {
            if (!$mboStatus['isInstalled']) {
                $installer->installModule();
            } elseif (!$mboStatus['isEnabled']) {
                $installer->enableModule();
            }
        } catch (\Exception $e) {
            $data[Installer::MODULE_NAME] = [
                'status' => false,
                'msg' => $e->getMessage(),
            ];
        }

        
        header('Content-type: application/json');
        echo json_encode($data);
        exit;
    }

    
    protected function buildDependenciesContext()
    {
        return [
            'module_display_name' => (string) $this->module->displayName,
            'module_name' => (string) $this->module->name,
            'module_version' => (string) $this->module->version,
            'ps_version' => (string) _PS_VERSION_,
            'php_version' => (string) PHP_VERSION,
            'locale' => $this->getLocale(),
            'dependencies' => $this->getDependencies(true),
        ];
    }

    
    private function getLocale()
    {
        $context = \ContextCore::getContext();
        if ($context !== null && $context->employee !== null) {
            $locale = \DbCore::getInstance()->getValue(
                sprintf(
                    'SELECT `locale` FROM `%slang` WHERE `id_lang`=%s',
                    _DB_PREFIX_,
                    pSQL((string) $context->employee->id_lang)
                )
            );
        }

        if (empty($locale)) {
            return 'en-GB';
        }

        return $locale;
    }

    
    protected function buildRoutesForModule($moduleName)
    {
        $urls = [];
        foreach (['install', 'enable', 'upgrade'] as $action) {
            $route = $this->router->generate('admin_module_manage_action', [
                'action' => $action,
                'module_name' => $moduleName,
            ]);

            if (is_string($route)) {
                $urls[$action] = $route;
            }
        }

        return $urls;
    }

    
    protected function buildRouter()
    {
        global $kernel;
        if (!$kernel instanceof \AppKernel) {
            throw new \Exception('Unable to retrieve Symfony AppKernel.');
        }

        $container = $kernel->getContainer();
        if (!$container instanceof ContainerInterface) {
            throw new \Exception('Unable to retrieve Symfony container.');
        }

        $router = $container->get('router');
        if (!$router instanceof Router) {
            throw new \Exception('Unable to retrieve Symfony router.');
        }
        $this->router = $router;
    }

    
    protected function addMboInDependencies($addRoutes = false)
    {
        if (!$this->isMboNeeded()) {
            return null;
        }

        $mboStatus = (new Presenter())->present();

        $specification = [
            'current_version' => (string) $mboStatus['version'],
            'installed' => (bool) $mboStatus['isInstalled'],
            'enabled' => (bool) $mboStatus['isEnabled'],
            'name' => Installer::MODULE_NAME,
        ];

        if (!$addRoutes) {
            return $specification;
        }

        return array_merge(
            $specification,
            $this->buildRoutesForModule(Installer::MODULE_NAME)
        );
    }

    
    protected function isMboNeeded()
    {
        return version_compare(_PS_VERSION_, '1.7.5', '>=');
    }

    
    private function getDependencies($addRoutes = false)
    {
        $dependenciesContent = $this->getDependenciesSpecification();

        if (empty($dependenciesContent['dependencies'])) {
            $mboDependency = $this->addMboInDependencies($addRoutes);
            if (null === $mboDependency) {
                return [];
            }

            return [
                Installer::MODULE_NAME => $mboDependency,
            ];
        }

        if ($this->isMboNeeded() && !isset($dependenciesContent['dependencies'][Installer::MODULE_NAME])) {
            $dependenciesContent['dependencies'][] = [
                'name' => Installer::MODULE_NAME,
            ];
        }

        $dependencies = [];
        foreach ($dependenciesContent['dependencies'] as $dependency) {
            if (!is_array($dependency) || !array_key_exists('name', $dependency)) {
                continue;
            }

            $dependencyData = \DbCore::getInstance()->getRow(
                sprintf(
                    'SELECT `id_module`, `active`, `version` FROM `%smodule` WHERE `name` = "%s"',
                    _DB_PREFIX_,
                    pSQL((string) $dependency['name'])
                )
            );

            if ($dependencyData && is_array($dependencyData) && !empty($dependencyData['id_module'])) {
                
                
                $isModuleActiveForAllShops = (bool) \DbCore::getInstance()->getValue(
                    sprintf('SELECT id_module
                            FROM `%smodule_shop`
                            WHERE id_module=%d AND id_shop IN (%s)
                            GROUP BY id_module
                            HAVING COUNT(*)=%d',
                        _DB_PREFIX_,
                        (int) $dependencyData['id_module'],
                        implode(', ', array_map('intval', \Shop::getContextListShopID())),
                        (int) count(\Shop::getContextListShopID())
                    )
                );

                $dependencyData['active'] = $isModuleActiveForAllShops;
            }

            if ($addRoutes) {
                $dependencies[$dependency['name']] = array_merge(
                    $dependency,
                    $this->buildRoutesForModule($dependency['name'])
                );
            } else {
                $dependencies[$dependency['name']] = $dependency;
            }

            if (!$dependencyData) {
                $dependencies[$dependency['name']]['installed'] = false;
                $dependencies[$dependency['name']]['enabled'] = false;
                continue;
            }
            $dependencies[$dependency['name']] = array_merge($dependencies[$dependency['name']], [
                'installed' => true,
                'enabled' => isset($dependencyData['active']) && (bool) $dependencyData['active'],
                'current_version' => isset($dependencyData['version']) ? $dependencyData['version'] : null,
            ]);
        }

        return $dependencies;
    }

    
    private function getDependenciesSpecification()
    {
        $dependencyFile = $this->module->getLocalPath() . self::DEPENDENCY_FILENAME;
        if (!file_exists($dependencyFile)) {
            throw new \Exception(self::DEPENDENCY_FILENAME . ' file is not found in ' . $this->module->getLocalPath());
        }

        if ($fileContent = file_get_contents($dependencyFile)) {
            $dependenciesContent = json_decode($fileContent, true);
        }
        if (
            !isset($dependenciesContent)
            || !is_array($dependenciesContent)
            || !array_key_exists('dependencies', $dependenciesContent)
            || json_last_error() != JSON_ERROR_NONE
        ) {
            throw new \Exception(self::DEPENDENCY_FILENAME . ' file may be malformed.');
        }

        return $dependenciesContent;
    }
}
