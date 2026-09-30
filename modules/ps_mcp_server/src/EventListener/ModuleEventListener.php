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

namespace PrestaShop\Module\PsMcpServer\EventListener;

use PrestaShop\Module\PsMcpServer\Services\McpService;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShopBundle\Event\ModuleManagementEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ModuleEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ModuleManagementEvent::INSTALL => 'storeNewModuleRegistered',
            ModuleManagementEvent::UNINSTALL => 'removeModuleRegistered',
            ModuleManagementEvent::DISABLE => 'removeModuleRegistered',
            ModuleManagementEvent::ENABLE => 'storeNewModuleRegistered',
            ModuleManagementEvent::UPGRADE => 'storeNewModuleRegistered',
        ];
    }

    private function getMcpService(): ?McpService
    {
        try {
            $container = SymfonyContainer::getInstance();

            if ($container === null) {
                return null;
            }

            $service = $container->get('PrestaShop\Module\PsMcpServer\Services\McpService');

            return $service instanceof McpService ? $service : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function storeNewModuleRegistered(ModuleManagementEvent $event): void
    {
        $moduleName = $event->getModule()->get('name');

        if ($moduleName === 'ps_mcp_server') {
            return;
        }

        try {
            $mcpService = $this->getMcpService();

            if ($mcpService === null) {
                return;
            }

            $module = \Module::getInstanceByName($moduleName);

            if (!$module) {
                throw new \PrestaShopException('Module not found');
            }

            if (method_exists($module, 'isMcpCompliant') && $module->isMcpCompliant()) {
                $mcpService->storeNewModuleRegistered((int) $module->id);
            }
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            throw new \PrestaShopException('Error while registering module to MCP: ' . $e->getMessage());
        }
    }

    public function removeModuleRegistered(ModuleManagementEvent $event): void
    {
        $moduleName = $event->getModule()->get('name');

        if ($moduleName === 'ps_mcp_server') {
            return;
        }

        try {
            $mcpService = $this->getMcpService();

            if ($mcpService === null) {
                return;
            }

            $module = \Module::getInstanceByName($moduleName);

            if (!$module) {
                throw new \PrestaShopException('Module not found');
            }

            if (method_exists($module, 'isMcpCompliant') && $module->isMcpCompliant()) {
                $mcpService->removeModuleRegistered((int) $module->id);
            }
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            throw new \PrestaShopException('Error while removing module from MCP: ' . $e->getMessage());
        }
    }
}
