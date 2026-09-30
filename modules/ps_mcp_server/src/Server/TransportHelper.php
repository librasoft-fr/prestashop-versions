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

namespace PrestaShop\Module\PsMcpServer\Server;

use PrestaShop\Module\PsMcpServer\Tracker\Segment;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TransportHelper
{
    public function getPendingNotifications(): array
    {
        $notifications = [
            'PS_MCP_SERVER_NEED_NOTIFY_TOOLS_CHANGED' => 'notifications/tools/list_changed',
            'PS_MCP_SERVER_NEED_NOTIFY_PROMPTS_CHANGED' => 'notifications/prompts/list_changed',
            'PS_MCP_SERVER_NEED_NOTIFY_RESOURCES_CHANGED' => 'notifications/resources/list_changed',
            'PS_MCP_SERVER_NEED_NOTIFY_RESOURCE_TEMPLATES_CHANGED' => 'notifications/resources/list_changed',
        ];

        $methods = [];

        foreach ($notifications as $configKey => $notificationPath) {
            if ((bool) \Configuration::get($configKey)) {
                $methods[] = $notificationPath;
                \Configuration::updateValue($configKey, false);
            }
        }

        return $methods;
    }

    public function checkDependencies(): ?string
    {
        $moduleManager = $this->getModuleManager();

        $psAccountsIsOk = $moduleManager->isInstalled('ps_accounts') && $moduleManager->isEnabled('ps_accounts');
        $psEventbusIsOk = $moduleManager->isInstalled('ps_eventbus') && $moduleManager->isEnabled('ps_eventbus');
        $psMcpToolsIsOk = $moduleManager->isInstalled('ps_mcp_tools') && $moduleManager->isEnabled('ps_mcp_tools');

        if (!$psAccountsIsOk || !$psEventbusIsOk || !$psMcpToolsIsOk) {
            $configurationUrl = \Configuration::get('PS_MCP_SERVER_CONFIGURATION_URL');

            return "Some of the dependencies required by the <b>ps_mcp_server</b> module are missing or disabled. Please visit <a href='{$configurationUrl}'>this page</a> to complete the setup.";
        }

        return null;
    }

    public function trackFirstUse(Segment $segment): void
    {
        $llmConnectedTracked = (bool) \Configuration::get('PS_MCP_SERVER_LLM_CONNECTED_TRACKED');

        if (!$llmConnectedTracked) {
            $segment->trackMessage('MCP LLM Connected', ['module_name' => 'ps_mcp_server']);

            \Configuration::updateValue('PS_MCP_SERVER_LLM_CONNECTED_TRACKED', true);
        }
    }

    private function getModuleManager(): ModuleManager
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();

        if ($moduleManagerBuilder == null) {
            throw new \PrestaShopException('ModuleManagerBuilder is not defined');
        }

        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager == null) {
            throw new \PrestaShopException('ModuleManager is not defined');
        }

        return $moduleManager;
    }
}
