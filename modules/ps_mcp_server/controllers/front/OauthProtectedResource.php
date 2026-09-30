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

use PrestaShop\Module\PsMcpServer\Exceptions\ContextException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_Mcp_ServerOauthProtectedResourceModuleFrontController extends ModuleFrontController
{
    public function initContent(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, Mcp-Protocol-Version, Mcp-Session-Id, Last-Event-ID');

        $authDisabled = (bool) Configuration::get('PS_MCP_SERVER_AUTH_DISABLED');

        if ($authDisabled) {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
            exit;
        }

        $context = Context::getContext();

        if ($context == null) {
            throw new PrestaShopException('Context is not defined');
        }

        $shopContext = $context->shop;

        if ($shopContext == null) {
            throw new ContextException();
        }

        $data = [
            'resource' => $shopContext->getBaseURL(true) . 'mcp',
            'authorization_servers' => [
                Ps_mcp_server::getConfig()['OAUTH_SERVER_URL'],
            ],
            'scopes_supported' => [
                'mcp.discover',
                'mcp.read',
                'mcp.write',
                'email',
            ],
            'resource_name' => $shopContext->getBaseURL(true) . ' - MCP powered by PrestaShop',
            'token_type' => 'Bearer',
        ];

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
