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
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_0()
{
    $db = Db::getInstance();

    if (!createNewUnifiedFeaturesTable($db) || !createModulesTable($db)) {
        return false;
    }

    if (!dropOldToolsTable($db)) {
        PrestaShopLogger::addLog('MCP Server: Could not drop old tools table, but migration was successful.');
    }

    if (!dropOldModulesRegisteredTable($db)) {
        PrestaShopLogger::addLog('MCP Server: Could not drop old modules registered table, but migration was successful.');
    }

    Configuration::deleteByName('PS_MCP_SERVER_TOOLS_DISCOVERED');

    Configuration::updateValue('PS_MCP_SERVER_FEATURES_NEED_DISCOVER', true);
    Configuration::updateValue('PS_MCP_SERVER_FIRST_DISCOVERY_DONE', false);

    return addTokenAndRoleColumnsToAllowedUsers($db);
}

function createNewUnifiedFeaturesTable($db)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mcp_server_features` (
        `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `module_id`        INT(10) UNSIGNED NOT NULL,
        `type`             ENUM(\'tool\', \'prompt\', \'resource\', \'resourceTemplate\') NOT NULL,
        `name`             VARCHAR(255)     NOT NULL,
        `description`      TEXT             NULL,
        `category`         VARCHAR(255)     NOT NULL DEFAULT \'general\',
        `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
        `created_at`       DATETIME         NOT NULL,
        `updated_at`       DATETIME         NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `feature_name_type` (`name`, `type`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8mb4;';

    return (bool) $db->execute($sql);
}

function createModulesTable($db)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mcp_server_modules` (
        `module_id`        INT(10) UNSIGNED NOT NULL,
        `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
        `created_at`       DATETIME         NOT NULL,
        PRIMARY KEY (`module_id`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8mb4;';

    return (bool) $db->execute($sql);
}

function dropOldToolsTable($db)
{
    return (bool) $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mcp_server_tools`');
}

function dropOldModulesRegisteredTable($db)
{
    return (bool) $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mcp_server_modules_registered`');
}

function addTokenAndRoleColumnsToAllowedUsers($db)
{
    $table = _DB_PREFIX_ . 'mcp_server_allowed_users';

    try {
        $columns = $db->executeS("SHOW COLUMNS FROM `{$table}` LIKE 'token_hash'");
        if (empty($columns)) {
            $db->execute("ALTER TABLE `{$table}` ADD `token_hash` VARCHAR(255) DEFAULT NULL AFTER `email`");
        }

        $columns = $db->executeS("SHOW COLUMNS FROM `{$table}` LIKE 'role'");
        if (empty($columns)) {
            $db->execute("ALTER TABLE `{$table}` ADD `role` ENUM('editor', 'viewer') NOT NULL DEFAULT 'viewer' AFTER `token_hash`");
        }

        $columns = $db->executeS("SHOW COLUMNS FROM `{$table}` LIKE 'updated_at'");
        if (empty($columns)) {
            $db->execute("ALTER TABLE `{$table}` ADD `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");
        }

        return true;
    } catch (Exception $e) {
        PrestaShopLogger::addLog('MCP Server: Failed to add token columns to allowed_users: ' . $e->getMessage());

        return false;
    }
}
