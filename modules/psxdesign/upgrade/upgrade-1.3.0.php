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

/**
 * @param PsxDesign $module
 *
 * @return bool
 */
function upgrade_module_1_3_0(PsxDesign $module): bool
{
    $db = Db::getInstance();
    $db->execute('START TRANSACTION;');

    if (createColorTable_1_3_0() && createColorPaletteTable_1_3_0() && transferDataToNewDatabaseTable_1_3_0() && dropOldPaletteTable_1_3_0()) {
        $module->registerHook('actionDispatcherBefore');
        $db->execute('COMMIT;');

        return true;
    }

    $db->execute('ROLLBACK;');

    return false;
}

function createColorTable_1_3_0(): bool
{
    $db = Db::getInstance();

    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_color` (
        `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
        `variable_name` VARCHAR(255) NOT NULL,
        `variable_type` ENUM("css_selector", "scss_variable", "css_variable") NOT NULL,
        `value` VARCHAR(7) NOT NULL,
        `id_palette` INT(10) UNSIGNED NOT NULL,
        PRIMARY KEY(`id`)
        ) ENGINE=`' . _MYSQL_ENGINE_ . '` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

    return $db->execute($sql);
}

function createColorPaletteTable_1_3_0(): bool
{
    $db = Db::getInstance();

    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_colors_palette` (
        `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
        `name` VARCHAR(64) NOT NULL,
        `active` TINYINT(1) DEFAULT 0 NOT NULL,
        `default` TINYINT(1) DEFAULT 0 NOT NULL,
        `theme` VARCHAR(64) NOT NULL,
        PRIMARY KEY(`id`)
        ) ENGINE=`' . _MYSQL_ENGINE_ . '` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

    return $db->execute($sql);
}

function transferDataToNewDatabaseTable_1_3_0(): bool
{
    $db = Db::getInstance();

    $oldData = $db->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'psxdesign_color_palette` WHERE `name` = "Theme colors"');

    return $db->execute('INSERT INTO `' . _DB_PREFIX_ . 'psxdesign_colors_palette` (`name`, `active`, `default`, `theme`) VALUES ("Theme colors", 1, 0, "classic")') &&
        $db->execute('INSERT INTO `' . _DB_PREFIX_ . 'psxdesign_color` (`variable_name`, `variable_type`, `value`, `id_palette`) VALUES ("$brand-primary", "scss_variable", "' . $oldData['primary'] . '", ' . pSQL(1) . ')') &&
        $db->execute('INSERT INTO `' . _DB_PREFIX_ . 'psxdesign_color` (`variable_name`, `variable_type`, `value`, `id_palette`) VALUES ("$brand-secondary", "scss_variable", "' . $oldData['secondary'] . '", ' . pSQL(1) . ')') &&
        $db->execute('INSERT INTO `' . _DB_PREFIX_ . 'psxdesign_color` (`variable_name`, `variable_type`, `value`, `id_palette`) VALUES ("$gray-darker", "scss_variable", "' . $oldData['text'] . '", ' . pSQL(1) . ')');
}

function dropOldPaletteTable_1_3_0(): bool
{
    return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'psxdesign_color_palette`');
}
