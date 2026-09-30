<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * This file is part of the PrestaShop Enterprise solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Tools\Mcp;

use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * MCP tools for routine shop maintenance: clearing caches, toggling maintenance
 * mode and rebuilding the product search index.
 *
 * The write tools change shop-wide behaviour (maintenance mode takes the
 * storefront offline), so they are not read-only and route through operator
 * approval.
 */
class MaintenanceTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'clear_cache',
        description: 'Clear all PrestaShop caches (Smarty, compiled templates, media/XML). Safe to run repeatedly.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'maintenance']
    )]
    #[PsMcpSchema(properties: [], required: [])]
    public function clearCache(): array
    {
        try {
            \Tools::clearAllCache();

            return ['content' => ['cleared' => true]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_maintenance_status',
        description: 'Report whether the storefront is online and, when in maintenance, the IPs allowed to bypass it.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'maintenance']
    )]
    #[PsMcpSchema(properties: [], required: [])]
    public function getMaintenanceStatus(): array
    {
        try {
            $online = (bool) \Configuration::get('PS_SHOP_ENABLE');

            return ['content' => [
                'shop_online' => $online,
                'maintenance_enabled' => !$online,
                'maintenance_allowed_ips' => (string) \Configuration::get('PS_MAINTENANCE_IP'),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'rebuild_search_index',
        description: 'Rebuild the full-text product search index. Use after bulk catalog changes so storefront search returns up-to-date results.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'maintenance']
    )]
    #[PsMcpSchema(properties: [], required: [])]
    public function rebuildSearchIndex(): array
    {
        try {
            $result = \Search::indexation(true);

            return ['content' => ['reindexed' => (bool) $result]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
