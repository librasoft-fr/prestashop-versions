<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * PE-1721: add the fork-tree columns to ps_ask_ai_conversation so an edited
 * message can spawn a child thread (a "variation") without touching the parent.
 *
 * Existing conversations are all roots: parent_thread_id stays NULL and
 * root_thread_id is backfilled to each row's own thread_id.
 */
function upgrade_module_1_0_6(Module $module): bool
{
    unset($module);

    $db = Db::getInstance();
    $prefix = _DB_PREFIX_;
    $table = "`{$prefix}ps_ask_ai_conversation`";

    // ALTER guarded per-column so a partial re-run is idempotent.
    $columns = [
        'parent_thread_id' => 'ADD COLUMN `parent_thread_id` VARCHAR(64) DEFAULT NULL AFTER `title`',
        'root_thread_id' => 'ADD COLUMN `root_thread_id` VARCHAR(64) NULL AFTER `parent_thread_id`',
        'branch_point' => 'ADD COLUMN `branch_point` INT DEFAULT NULL AFTER `root_thread_id`',
        'active_leaf_thread_id' => 'ADD COLUMN `active_leaf_thread_id` VARCHAR(64) DEFAULT NULL AFTER `branch_point`',
    ];

    $existing = [];
    foreach ($db->executeS("SHOW COLUMNS FROM {$table}") ?: [] as $row) {
        $existing[(string) $row['Field']] = true;
    }

    foreach ($columns as $name => $clause) {
        if (isset($existing[$name])) {
            continue;
        }
        if (!$db->execute("ALTER TABLE {$table} {$clause}")) {
            return false;
        }
    }

    // Backfill: every pre-existing conversation is its own tree root.
    if (!$db->execute("UPDATE {$table} SET `root_thread_id` = `thread_id` WHERE `root_thread_id` IS NULL OR `root_thread_id` = ''")) {
        return false;
    }

    // Now that it is populated, enforce NOT NULL to match a fresh install.
    if (!$db->execute("ALTER TABLE {$table} MODIFY `root_thread_id` VARCHAR(64) NOT NULL")) {
        return false;
    }

    // Best-effort index; ignore failure if it already exists.
    $hasIndex = false;
    foreach ($db->executeS("SHOW INDEX FROM {$table}") ?: [] as $row) {
        if ((string) $row['Key_name'] === 'idx_root_thread_id') {
            $hasIndex = true;
            break;
        }
    }
    if (!$hasIndex) {
        $db->execute("ALTER TABLE {$table} ADD INDEX `idx_root_thread_id` (`root_thread_id`)");
    }

    return true;
}
