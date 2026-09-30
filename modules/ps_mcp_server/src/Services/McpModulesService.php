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

use PrestaShop\Module\PsMcpServer\Repository\McpModulesRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpModulesService
{
    private $repository;

    public function __construct(McpModulesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllModules(): array
    {
        return $this->repository->getAllModules();
    }

    public function registerModule(int $moduleId, bool $isActive = true): bool
    {
        return $this->repository->registerModule($moduleId, $isActive);
    }

    public function getModuleById(int $moduleId): array|false
    {
        return $this->repository->getModuleById($moduleId);
    }

    public function updateModuleStatus(int $moduleId, bool $isActive): bool
    {
        return $this->repository->updateModuleStatus($moduleId, $isActive);
    }

    public function isModuleActive(int $moduleId): bool
    {
        return $this->repository->isModuleActive($moduleId);
    }

    public function deleteModuleById(int $moduleId): bool
    {
        return $this->repository->deleteModuleById($moduleId);
    }
}
