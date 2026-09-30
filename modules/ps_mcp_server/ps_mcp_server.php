<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\Module\PsMcpServer\Services\SentryService;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Domain\Module\Exception\ModuleException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_mcp_server extends Module
{
    use PrestaShop\Module\PsMcpServer\Traits\UseHooks;

    private $serviceContainer;

    public $adminControllers;

    public $version;

    public $multistoreCompatibility;

    public $emailSupport;

    public $termsOfServiceUrl;

    public function __construct()
    {
        $this->name = 'ps_mcp_server';
        $this->author = 'PrestaShop SA';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->version = '1.0.3';
        $this->module_key = '7da9741326f235014ecd8285c7a5f7dc';

        parent::__construct();

        $this->emailSupport = 'cloudsync-support@prestashop.com';
        $this->termsOfServiceUrl = 'https://www.prestashop.com/en/prestashop-account-privacy';
        $this->displayName = $this->l('PrestaShop MCP Server');
        $this->description = $this->l('Connect your store with LLM through MCP server.');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '8.2', 'max' => _PS_VERSION_];
        $this->adminControllers = [];

        if ($this->serviceContainer === null) {
            $this->serviceContainer = new ServiceContainer(
                (string) $this->name,
                $this->getLocalPath()
            );
        }

        $this->initSentry();
    }

    private function initSentry(): void
    {
        try {
            $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
            if ($moduleManagerBuilder == null) {
                throw new ModuleException('ModuleManagerBuilder is not defined');
            }

            $moduleManager = $moduleManagerBuilder->build();

            if (!$moduleManager->isInstalled((string) $this->name)) {
                throw new ModuleException('Module is not installed');
            }

            $sentryService = $this->getService(SentryService::class);

            if ($sentryService !== null && self::getConfig()['SENTRY_ENVIRONMENT'] !== null) {
                $sentryService->init(self::getConfig()['SENTRY_ENVIRONMENT'], $this->version, self::getConfig()['SENTRY_DSN']);
            }
        } catch (Exception $e) {
            return;
        }
    }

    public function install(): bool
    {
        return parent::install()
            && $this->installDatabaseTables()
            && $this->registerHook($this->getHooksList())
            && $this->addConfiguration()
        ;
    }

    public function uninstall(): bool
    {
        foreach ($this->getHooksList() as $hook) {
            if (!$this->unregisterHook($hook)) {
                return false;
            }
        }

        return parent::uninstall()
            && $this->uninstallDatabaseTables()
            && $this->removeConfiguration()
        ;
    }

    public function disable($force_all = false): bool
    {
        return parent::disable($force_all)
            && Configuration::updateValue('PS_MCP_SERVER_STARTED', false);
    }

    public function enable($force_all = false): bool
    {
        return parent::enable($force_all)
            && Configuration::updateValue('PS_MCP_SERVER_STARTED', true);
    }

    public function upgrade(): bool
    {
        Configuration::updateValue('PS_MCP_SERVER_FIRST_DISCOVERY_DONE', false);

        Tools::clearSf2Cache();

        return true;
    }

    public function getMultistoreCompatibility(): int
    {
        return (int) true;
    }

    public function getContent(): void
    {
        if ($this->context == null) {
            throw new PrestaShopException('Context is not defined');
        }

        if ($this->context->link == null) {
            throw new PrestaShopException('Link context is not defined');
        }

        try {
            $url = $this->context->link->getAdminLink('McpServerConfigController', true, ['route' => 'ps_mcp_server_config']);
        } catch (Exception $e) {
            Tools::clearSf2Cache();
            $url = $this->context->link->getAdminLink('McpServerConfigController', true, ['route' => 'ps_mcp_server_config']);
        }

        Tools::redirectAdmin($url);
    }

    public static function getConfig(): array
    {
        $configFile = __DIR__ . '/config.php';

        if (file_exists($configFile)) {
            $config = include $configFile;
        }

        return $config ?? [];
    }

    public function getService($serviceName)
    {
        if (version_compare(_PS_VERSION_, '9.0', '>=')) {
            return $this->get($serviceName);
        }

        $splitServiceNamespace = explode('.', $serviceName);
        $firstLevelNamespace = $splitServiceNamespace[0];

        if ($firstLevelNamespace !== 'ps_metrics' && $firstLevelNamespace !== 'ps_accounts') {
            try {
                $service = $this->serviceContainer->getService($serviceName);
            } catch (Exception $e) {
                $container = SymfonyContainer::getInstance();

                if ($container == null) {
                    throw new PrestaShopException('Symfony container is null or invalid');
                }

                $service = $container->get($serviceName);
            }

            return $service;
        }

        return $this->serviceContainer->getService($serviceName);
    }

    public function tableExist(string $tableName): bool
    {
        $table = _DB_PREFIX_ . $tableName;
        $query = 'SHOW TABLES LIKE \'' . $table . '\'';

        return (bool) Db::getInstance()->executeS($query);
    }

    public function installDatabaseTables(): bool
    {
        $dbInstallFile = __DIR__ . '/sql/install.sql';

        if (!file_exists($dbInstallFile)) {
            return false;
        }

        $sql = (string) Tools::file_get_contents($dbInstallFile);

        if (empty($sql)) {
            return false;
        }

        $sql = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);
        $sql = preg_split("/;\s*[\r\n]+/", trim($sql));

        $success = true;

        if (!empty($sql)) {
            foreach ($sql as $query) {
                if (!Db::getInstance()->execute($query)) {
                    $success = false;
                    break;
                }
            }
        }

        return $success;
    }

    private function uninstallDatabaseTables(): bool
    {
        $dbUninstallFile = __DIR__ . '/sql/uninstall.sql';

        if (!file_exists($dbUninstallFile)) {
            return false;
        }

        $sql = (string) Tools::file_get_contents($dbUninstallFile);

        if (empty($sql)) {
            return false;
        }

        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", trim($sql));

        $success = true;

        if (!empty($sql)) {
            foreach ($sql as $query) {
                if (!Db::getInstance()->execute($query)) {
                    $success = false;
                    break;
                }
            }
        }

        return $success;
    }

    private function addConfiguration(): bool
    {
        return Configuration::updateValue('PS_MCP_SERVER_STARTED', true)
            && Configuration::updateValue('PS_MCP_SERVER_FIRST_DISCOVERY_DONE', false)
            && Configuration::updateValue('PS_MCP_SERVER_FEATURES_NEED_DISCOVER', true)
            && Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_TOOLS_CHANGED', false)
            && Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_PROMPTS_CHANGED', false)
            && Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCES_CHANGED', false)
            && Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCE_TEMPLATES_CHANGED', false)
            && Configuration::updateValue('PS_MCP_SERVER_HOT_CACHING_ENABLED', false)
            && Configuration::updateValue('PS_MCP_SERVER_LOGS_ENABLED', false)
            && Configuration::updateValue('PS_MCP_SERVER_CONFIGURATION_URL', '')
            && Configuration::updateValue('PS_MCP_SERVER_CLOUDSYNC_ALREADY_ONBOARDED', false)
            && Configuration::updateValue('PS_MCP_SERVER_ANON_TRACKING', '')
            && Configuration::updateValue('PS_MCP_SERVER_ONBOARDING_TRACKED', false)
            && Configuration::updateValue('PS_MCP_SERVER_LLM_CONNECTED_TRACKED', false)
            && Configuration::updateValue('PS_MCP_SERVER_ACCOUNTS_ONBOARDING_TRACKED', false)
            && Configuration::updateValue('PS_MCP_SERVER_AUTH_DISABLED', false)
        ;
    }

    private function removeConfiguration(): bool
    {
        return Configuration::deleteByName('PS_MCP_SERVER_STARTED')
            && Configuration::deleteByName('PS_MCP_SERVER_FIRST_DISCOVERY_DONE')
            && Configuration::deleteByName('PS_MCP_SERVER_FEATURES_NEED_DISCOVER')
            && Configuration::deleteByName('PS_MCP_SERVER_NEED_NOTIFY_TOOLS_CHANGED')
            && Configuration::deleteByName('PS_MCP_SERVER_NEED_NOTIFY_PROMPTS_CHANGED')
            && Configuration::deleteByName('PS_MCP_SERVER_NEED_NOTIFY_RESOURCES_CHANGED')
            && Configuration::deleteByName('PS_MCP_SERVER_NEED_NOTIFY_RESOURCE_TEMPLATES_CHANGED')
            && Configuration::deleteByName('PS_MCP_SERVER_HOT_CACHING_ENABLED')
            && Configuration::deleteByName('PS_MCP_SERVER_LOGS_ENABLED')
            && Configuration::deleteByName('PS_MCP_SERVER_CONFIGURATION_URL')
            && Configuration::deleteByName('PS_MCP_SERVER_CLOUDSYNC_ALREADY_ONBOARDED')
            && Configuration::deleteByName('PS_MCP_SERVER_ANON_TRACKING')
            && Configuration::deleteByName('PS_MCP_SERVER_ONBOARDING_TRACKED')
            && Configuration::deleteByName('PS_MCP_SERVER_LLM_CONNECTED_TRACKED')
            && Configuration::deleteByName('PS_MCP_SERVER_ACCOUNTS_ONBOARDING_TRACKED')
            && Configuration::deleteByName('PS_MCP_SERVER_AUTH_DISABLED')
        ;
    }
}
