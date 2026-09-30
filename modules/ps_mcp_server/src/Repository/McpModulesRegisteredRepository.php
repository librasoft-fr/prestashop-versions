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

namespace PrestaShop\Module\PsMcpServer\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpModulesRegisteredRepository
{
    public const TABLE_NAME = 'mcp_server_modules_registered';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $db;

    public function __construct()
    {
        $this->db = \Db::getInstance();
    }

    public function getAllModules(): array
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME, 'mcp_smr')
            ->orderBy('mcp_smr.created_at DESC');

        $result = $this->db->executeS($query);

        return is_array($result) ? $result : [];
    }

    public function addModuleId(int $moduleId): bool
    {
        $query = new \DbQuery();

        $query->select('*')
            ->from(self::TABLE_NAME, 'mcp_smr')
            ->where("mcp_smr.module_id = '" . (int) $moduleId . "'");

        $existingModule = (array) $this->db->executeS($query);

        if (count($existingModule) > 0) {
            return false;
        }

        $data = [
            'module_id' => (int) $moduleId,
            'created_at' => date(self::DATETIME_FORMAT),
        ];

        return $this->db->insert(self::TABLE_NAME, $data);
    }

    public function getModuleById(int $moduleId): array|false
    {
        $query = new \DbQuery();

        $query->select('*')
            ->from(self::TABLE_NAME, 'mcp_smr')
            ->where('mcp_smr.module_id = ' . (int) $moduleId);

        $result = $this->db->getRow($query);

        return is_array($result) ? $result : false;
    }

    public function deleteModuleById(int $moduleId): bool
    {
        return $this->db->delete(self::TABLE_NAME, 'module_id = "' . (int) $moduleId . '"');
    }
}
