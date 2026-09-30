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

class McpFeaturesRepository
{
    public const TABLE_NAME = 'mcp_server_features';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $db;

    public function __construct()
    {
        $this->db = \Db::getInstance();
    }

    public function getFeatureByNameAndType(string $name, string $type): array|false
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('type = "' . pSQL($type) . '"')
            ->where('name = "' . pSQL($name) . '"');

        $result = $this->db->getRow($query);

        return is_array($result) ? $result : false;
    }

    public function upsertFeature(int $moduleId, string $type, string $name, string $description, string $category = 'general', bool $isActive = true): bool
    {
        $data = [
            'module_id' => (int) $moduleId,
            'type' => pSQL($type),
            'name' => pSQL($name),
            'description' => pSQL($description),
            'category' => pSQL($category),
            'is_active' => $isActive ? 1 : 0,
            'updated_at' => date(self::DATETIME_FORMAT),
        ];

        $existing = $this->getFeatureByNameAndType($name, $type);

        if ($existing) {
            return $this->db->update(self::TABLE_NAME, $data, 'id = ' . (int) $existing['id']);
        }

        $data['created_at'] = date(self::DATETIME_FORMAT);

        return $this->db->insert(self::TABLE_NAME, $data);
    }

    public function getFeaturesByType(string $type): array
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('type = "' . pSQL($type) . '"');

        return (array) $this->db->executeS($query) ?: [];
    }

    public function getAllFeatures(bool $onlyActive = false): array
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME);

        if ($onlyActive) {
            $query->where('is_active = 1');
        }

        return (array) $this->db->executeS($query) ?: [];
    }

    public function updateFeatureStatus(int $moduleId, string $type, string $name, bool $isActive): bool
    {
        return $this->db->update(
            self::TABLE_NAME,
            [
                'is_active' => $isActive ? 1 : 0,
                'updated_at' => date(self::DATETIME_FORMAT),
            ],
            'module_id = ' . (int) $moduleId . ' AND type = "' . pSQL($type) . '" AND name = "' . pSQL($name) . '"'
        );
    }

    public function updateFeaturesStatusBatch(array $features, bool $isActive): bool
    {
        if (empty($features)) {
            return true;
        }

        $conditions = [];
        foreach ($features as $feature) {
            $conditions[] = '(module_id = ' . (int) $feature['moduleId']
                . ' AND type = "' . pSQL($feature['type'])
                . '" AND name = "' . pSQL($feature['name']) . '")';
        }

        return $this->db->update(
            self::TABLE_NAME,
            [
                'is_active' => $isActive ? 1 : 0,
                'updated_at' => date(self::DATETIME_FORMAT),
            ],
            implode(' OR ', $conditions)
        );
    }

    public function deleteFeature(string $name, int $moduleId, string $type): bool
    {
        return $this->db->delete(
            self::TABLE_NAME,
            'name = "' . pSQL($name) . '" AND module_id = ' . (int) $moduleId . ' AND type = "' . pSQL($type) . '"'
        );
    }

    public function deleteAllModuleFeatures(int $moduleId, ?string $type = null): bool
    {
        $where = 'module_id = ' . (int) $moduleId;
        if ($type) {
            $where .= ' AND type = "' . pSQL($type) . '"';
        }

        return $this->db->delete(self::TABLE_NAME, $where);
    }
}
