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

function upgrade_module_0_3_0()
{
    $db = Db::getInstance();

    try {
        $db->query('
            ALTER TABLE `' . _DB_PREFIX_ . 'mcp_server_allowed_users`
            ADD `token_hash` VARCHAR(255) NOT NULL AFTER `email`;
        ');

        $db->query('
            ALTER TABLE `' . _DB_PREFIX_ . 'mcp_server_allowed_users`
            ADD `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
        ');

        return true;
    } catch (Exception $e) {
        return false;
    }
}
