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

namespace PrestaShop\Module\PsMcpServer\Helper;

use PrestaShop\Module\PsMcpServer\Http\Client;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ModuleHelper
{
    private const ADDONS_API_URL = 'https://api-addons.prestashop.com';

    private const ADDONS_MODULE_IDS = [
        'ps_mcp_tools' => 96472,
        'ps_mcp_server' => 96617,
        'ps_accounts' => 49648,
        'ps_eventbus' => 50756,
    ];

    public function buildModuleInformation(string $technicalName, ?string $nameIfMissing = null): array
    {
        $baseUrl = \Tools::getShopDomainSsl(true, true);

        return [
            'technicalName' => $technicalName,
            'name' => $this->isInstalled($technicalName) ? \ModuleCore::getModuleName($technicalName) : ($nameIfMissing ?? 'Unknown Module'),
            'logo' => $this->isInstalled($technicalName) ? $baseUrl . __PS_BASE_URI__ . 'modules/' . $technicalName . '/logo.png' : $baseUrl . __PS_BASE_URI__ . 'modules/ps_mcp_server/views/img/ps_logo.jpeg',
            'installState' => $this->isInstalled($technicalName)
                ? ($this->isEnabled($technicalName) ? 'installed' : 'disabled')
                : 'uninstalled',
            'version' => $this->getModuleVersion($technicalName),
        ];
    }

    private function isInstalled(string $moduleName)
    {
        return \ModuleCore::isInstalled($moduleName);
    }

    private function isEnabled(string $moduleName)
    {
        return \ModuleCore::isEnabled($moduleName);
    }

    private function getModuleVersion(string $moduleName)
    {
        if (false === $this->isInstalled($moduleName)) {
            return '0.0.0';
        }

        $module = \Module::getInstanceByName($moduleName);

        if (false === $module) {
            return '0.0.0';
        }

        return $module->version;
    }

    public function downloadModuleFromAddons(string $technicalName): string
    {
        if (!isset(self::ADDONS_MODULE_IDS[$technicalName])) {
            throw new \PrestaShopException('No Addons ID configured for module: ' . $technicalName);
        }

        $url = self::ADDONS_API_URL . '/?' . http_build_query([
            'id_module' => self::ADDONS_MODULE_IDS[$technicalName],
            'channel' => 'stable',
            'method' => 'module',
            'version' => _PS_VERSION_,
        ]);

        $client = Client::getInstance();
        $client->setTimeout(60);
        $client->get($url, []);

        if (!$client->isSuccess()) {
            throw new \PrestaShopException('Failed to download module from Addons API (HTTP ' . $client->getHttpStatus() . ')');
        }

        $body = $client->getResponse();

        if ($body == false || $body == null || $body == '') {
            throw new \PrestaShopException('Empty response from Addons API');
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'mod');

        if ($temporaryFile === false) {
            throw new \PrestaShopException('Cannot create temporary file in ' . sys_get_temp_dir());
        }

        if (file_put_contents($temporaryFile, $body) === false) {
            throw new \PrestaShopException('Cannot write module data to temporary file');
        }

        return $temporaryFile;
    }
}
