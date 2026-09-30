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

namespace PrestaShop\Module\PsMcpServer\Services;

use PrestaShop\Module\PsMcpServer\Repository\McpFeaturesRepository;
use PrestaShop\Module\PsMcpServer\Repository\McpModulesRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpFeaturesService
{
    private $mcpFeaturesRepository;

    private $mcpModulesRepository;

    public function __construct(McpFeaturesRepository $mcpFeaturesRepository, McpModulesRepository $mcpModulesRepository)
    {
        $this->mcpFeaturesRepository = $mcpFeaturesRepository;
        $this->mcpModulesRepository = $mcpModulesRepository;
    }

    public function registerFeature(string $type, int $moduleId, string $name, string $description, string $category = 'general'): bool
    {
        $this->mcpModulesRepository->registerModule($moduleId);

        return $this->mcpFeaturesRepository->upsertFeature(
            $moduleId,
            $type,
            $name,
            $description,
            $category,
            true
        );
    }

    public function getAllModuleFeatures(): array
    {
        $allFeatures = $this->mcpFeaturesRepository->getAllFeatures();
        $groupedByModule = [];

        foreach ($allFeatures as $feature) {
            $module = \Module::getInstanceById($feature['module_id']);
            if (!$module) {
                continue;
            }

            $moduleName = $module->displayName;
            $moduleId = (int) $module->id;

            if (!isset($groupedByModule[$moduleId])) {
                $moduleLogo = \Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/logo.png');
                $moduleRecord = $this->mcpModulesRepository->getModuleById($moduleId);

                $groupedByModule[$moduleId] = [
                    'module_name' => $moduleName,
                    'module_technical_name' => $module->name,
                    'module_id' => $moduleId,
                    'module_logo' => $moduleLogo ?: '',
                    'is_active' => $moduleRecord ? (bool) $moduleRecord['is_active'] : true,
                    'features' => [
                        'tools' => [],
                        'prompts' => [],
                        'resources' => [],
                        'resourceTemplates' => [],
                    ],
                ];
            }

            $groupedByModule[$moduleId]['features'][$feature['type'] . 's'][] = [
                'name' => $feature['name'],
                'description' => $feature['description'],
                'category' => $feature['category'] ?? 'general',
                'is_active' => (bool) $feature['is_active'],
                'type' => $feature['type'],
            ];
        }

        return array_values($groupedByModule);
    }

    public function updateFeatureStatus(string $type, int $moduleId, string $name, bool $isActive): bool
    {
        return $this->mcpFeaturesRepository->updateFeatureStatus($moduleId, $type, $name, $isActive);
    }

    public function updateFeaturesStatusBatch(array $features, bool $isActive): bool
    {
        return $this->mcpFeaturesRepository->updateFeaturesStatusBatch($features, $isActive);
    }

    public function updateModuleStatus(int $moduleId, bool $isActive): bool
    {
        return $this->mcpModulesRepository->updateModuleStatus($moduleId, $isActive);
    }

    public function isModuleActive(int $moduleId): bool
    {
        return $this->mcpModulesRepository->isModuleActive($moduleId);
    }

    public function deleteAllModuleFeatures(int $moduleId): bool
    {
        return $this->mcpFeaturesRepository->deleteAllModuleFeatures($moduleId);
    }

    public function cleanObsoleteFeatures(string $type, array $discoveredFeatures): void
    {
        $allFromDb = $this->mcpFeaturesRepository->getFeaturesByType($type);

        $discoveredNames = array_column($discoveredFeatures, 'name');
        foreach ($allFromDb as $featureFromDb) {
            if (!in_array($featureFromDb['name'], $discoveredNames)) {
                $this->mcpFeaturesRepository->deleteFeature(
                    $featureFromDb['name'],
                    (int) $featureFromDb['module_id'],
                    $type
                );
            }
        }
    }

    public function getFeatureByNameAndType(string $type, string $name): array|false
    {
        return $this->mcpFeaturesRepository->getFeatureByNameAndType($name, $type);
    }
}
