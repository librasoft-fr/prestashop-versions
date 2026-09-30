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

use PrestaShop\Module\PsMcpServer\Services\McpService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_mcp_serverMcpServerModuleFrontController extends ModuleFrontController
{
    public $module;

    public function initContent(): void
    {
        $this->rateLimit();

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, Mcp-Protocol-Version, Mcp-Session-Id, Last-Event-ID');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');

        $mcpService = $this->module->getService(McpService::class);

        if ($mcpService == null) {
            throw new PrestaShopException('McpService is not available');
        }

        $mcpService->executeHttpMcpRequest();
    }

    private function rateLimit(): void
    {
        $clientIp = Tools::getRemoteAddr();
        $cacheKey = 'mcp_rate_' . md5($clientIp);
        $cache = new Symfony\Component\Cache\Adapter\FilesystemAdapter('rate', 60);

        $item = $cache->getItem($cacheKey);
        $count = $item->isHit() ? (int) $item->get() : 0;

        if ($count > 100) {
            http_response_code(429);
            header('Retry-After: 60');
            echo json_encode(['error' => 'Too many requests']);
            exit;
        }

        $item->set($count + 1);
        $item->expiresAfter(60);
        $cache->save($item);
    }
}
