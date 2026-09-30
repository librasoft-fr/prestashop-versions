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

namespace PrestaShop\Module\PsMcpServer\Traits;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait UseHooks
{
    public function getHooksList(): array
    {
        return [
            'moduleRoutes',
            'displayBackOfficeHeader',
        ];
    }

    public function hookModuleRoutes(): array
    {
        return [
            'ps_mcp_server-mcp-server' => [
                'controller' => 'McpServer',
                'rule' => 'mcp',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
            'ps_mcp_server-wellknown-oauth-protected-resource' => [
                'controller' => 'OauthProtectedResource',
                'rule' => '.well-known/oauth-protected-resource',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
        ];
    }

    public function hookDisplayBackOfficeHeader(): string
    {
        $twig = $this->get('twig');

        if ($twig == null) {
            return '';
        }

        $context = \Context::getContext();
        $link = $context ? $context->link : null;

        if ($context == null || $context->controller == null || $link == null) {
            return '';
        }

        return $twig->render(
            '@Modules/ps_mcp_server/views/templates/admin/notification-bar.html.twig',
            [
                'LIVE_MODE_VUEJS' => (bool) \Ps_mcp_server::getConfig()['LIVE_MODE_VUEJS'],
                'wc_js_url' => $this->getWcJsUrl(),
                'wc_css_url' => $this->getWcCssUrl(),
                'current_version' => $this->version,
                'module_manager_url' => $link->getAdminLink('AdminModules', true, ['route' => 'admin_module_manage']),
            ]
        );
    }

    private function getWcJsUrl(): string
    {
        $context = \Context::getContext();

        if ($context === null || $context->shop === null) {
            return '';
        }

        $publicBase = rtrim((string) $context->shop->getBaseURL(true), '/') . '/modules/ps_mcp_server/views/dist/';

        return $publicBase . 'notification-banner-wc.js';
    }

    private function getWcCssUrl(): string
    {
        $context = \Context::getContext();

        if ($context === null || $context->shop === null) {
            return '';
        }

        $publicBase = rtrim((string) $context->shop->getBaseURL(true), '/') . '/modules/ps_mcp_server/views/dist/';

        return $publicBase . 'main-wc.css';
    }
}
