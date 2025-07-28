<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$db = Db::getInstance();

$sql_queries = [];

// colors palette table
$sql_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_colors_palette` (
    `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
    `name` VARCHAR(64) NOT NULL,
    `active` TINYINT(1) DEFAULT 0 NOT NULL,
    `default` TINYINT(1) DEFAULT 0 NOT NULL,
    `theme` VARCHAR(64) NOT NULL,
    PRIMARY KEY(`id`)
) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

// colors table
$sql_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_color` (
    `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
    `variable_name` VARCHAR(255) NOT NULL,
    `variable_type` enum("css_selector", "scss_variable", "css_variable") NOT NULL,
    `value` VARCHAR(7) NOT NULL,
    `id_palette` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY (`id_palette`) REFERENCES `' . _DB_PREFIX_ . 'psxdesign_colors_palette` (`id`)
) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

// fonts table
$sql_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_fonts` (
    `id` INT(10) UNSIGNED AUTO_INCREMENT  NOT NULL,
    `variable_name` VARCHAR(255) NOT NULL,
    `variable_type` VARCHAR(64) NOT NULL,
    `font` VARCHAR(64) NOT NULL,
    `style` VARCHAR(64) NOT NULL,
    `size` INT(10) NOT NULL,
    `theme_name` VARCHAR(64) NOT NULL,
    PRIMARY KEY(id)
) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

// logo table
$sql_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_logo` (
    `id` INT(10) UNSIGNED AUTO_INCREMENT  NOT NULL,
    `logo_destination` enum("header", "email", "invoice") NOT NULL,
    `logo_type` enum("text", "image") NOT NULL,
    `text` VARCHAR(64),
    `font` VARCHAR(64),
    `use_header_logo` tinyint(1),
    `font_size` INT(10),
    `color` VARCHAR(64),
    `style` VARCHAR(64),
    `active` tinyint(1),
    PRIMARY KEY(id)
) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

foreach ($sql_queries as $sql) {
    if (!$db->execute($sql)) {
        return false;
    }
}
