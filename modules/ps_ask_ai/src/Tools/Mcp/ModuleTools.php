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
 * MCP tools to inspect and toggle modules.
 *
 * Listing reads the installed `module` table directly (no addons-marketplace
 * round-trip, so it stays fast and offline). Enabling/disabling a module changes
 * shop behaviour, so set_module_status is not read-only and routes through
 * operator approval.
 */
class ModuleTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'list_modules',
        description: 'List installed modules with their technical name, version and active state. Default limit 100.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'modules']
    )]
    #[PsMcpSchema(
        properties: [
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only enabled modules (default false)'],
            'search' => ['type' => ['string', 'null'], 'description' => 'Filter by technical name substring (optional)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 100)'],
        ],
        required: []
    )]
    public function listModules(bool $activeOnly = false, ?string $search = null, int $limit = 100): array
    {
        try {
            $query = new \DbQuery();
            $query->select('m.id_module, m.name, m.active, m.version')
                ->from('module', 'm');
            if ($activeOnly) {
                $query->where('m.active = 1');
            }
            if ($search !== null && trim($search) !== '') {
                $query->where("m.name LIKE '%" . pSQL(trim($search)) . "%'");
            }
            $query->orderBy('m.name ASC')->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_module' => (int) $r['id_module'],
                    'name' => (string) $r['name'],
                    'version' => (string) $r['version'],
                    'active' => (bool) $r['active'],
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'set_module_status',
        description: 'Enable or disable an installed module by its technical name (e.g. "ps_featuredproducts").',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: true,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'modules']
    )]
    #[PsMcpSchema(
        properties: [
            'name' => ['type' => 'string', 'description' => 'Technical module name (e.g. "ps_featuredproducts")'],
            'enabled' => ['type' => 'boolean', 'description' => 'true to enable, false to disable'],
        ],
        required: ['name', 'enabled']
    )]
    public function setModuleStatus(string $name, bool $enabled): array
    {
        try {
            $name = trim($name);
            if (!\Module::isInstalled($name)) {
                throw new \RuntimeException(sprintf('Module "%s" is not installed.', $name));
            }

            $module = \Module::getInstanceByName($name);
            if (!$module instanceof \Module) {
                throw new \RuntimeException(sprintf('Could not load module "%s".', $name));
            }

            $ok = $enabled ? $module->enable() : $module->disable();
            if (!$ok) {
                throw new \RuntimeException(sprintf('Failed to %s module "%s".', $enabled ? 'enable' : 'disable', $name));
            }

            return ['content' => ['name' => $name, 'active' => $enabled]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
