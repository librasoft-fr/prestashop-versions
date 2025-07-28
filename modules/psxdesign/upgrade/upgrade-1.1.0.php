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
function upgrade_module_1_1_0(PsxDesign $module): bool
{
    $tabs = getTabs_1_1_0($module);
    createColorPaletteDb_1_1_0();

    return uninstallTabs_1_1_0($tabs) &&
        installTabs_1_1_0($tabs, $module) &&
        $module->registerHook('actionObjectTabUpdateAfter') &&
        $module->registerHook('actionAdminControllerSetMedia') &&
        modifyAdminThemesTab_1_1_0($module);
}

function getTabs_1_1_0($module): array
{
    $translator = $module->getTranslator();

    return [
        [
            'class_name' => 'AdminPsxDesignParentTab',
            'visible' => true,
            'name' => $translator->trans('Theme settings', [], 'Modules.Psxdesign.Admin'),
            'route_name' => '',
            'parent_class_name' => 'AdminParentThemes',
            'wording' => 'Theme settings',
            'wording_domain' => 'Admin.Modules.PsxDesign',
            'position' => 0,
        ],
        [
            'class_name' => 'AdminPsxDesignThemeGeneral',
            'visible' => true,
            'name' => $translator->trans('Themes', [], 'Modules.Psxdesign.Admin'),
            'route_name' => 'admin_psxdesign_themes_index',
            'parent_class_name' => 'AdminPsxDesignParentTab',
            'wording' => 'Themes',
            'wording_domain' => 'Admin.Modules.PsxDesign',
            'position' => 0,
        ],
        [
            'class_name' => 'AdminPsxDesignLogos',
            'visible' => true,
            'name' => $translator->trans('Logos', [], 'Modules.Psxdesign.Admin'),
            'route_name' => 'admin_logos_index',
            'parent_class_name' => 'AdminPsxDesignParentTab',
            'wording' => 'Logos',
            'wording_domain' => 'Admin.Modules.PsxDesign',
            'position' => 1,
        ],
        [
            'class_name' => 'AdminPsxDesignColors',
            'visible' => true,
            'name' => $translator->trans('Colors', [], 'Modules.Psxdesign.Admin'),
            'route_name' => 'admin_colors_index',
            'parent_class_name' => 'AdminPsxDesignParentTab',
            'wording' => 'Colors',
            'wording_domain' => 'Admin.Modules.PsxDesign',
            'position' => 2,
        ],
    ];
}

/**
 * @param array $tabs
 *
 * @return bool
 */
function uninstallTabs_1_1_0(array $tabs): bool
{
    foreach ($tabs as $tab) {
        $tabId = (int) Tab::getInstanceFromClassName($tab['class_name'])->id;

        if (!$tabId) {
            continue;
        }

        $tab = new Tab($tabId);
        $tab->delete();
    }

    return true;
}

function installTabs_1_1_0(array $tabs, PsxDesign $module): bool
{
    $translator = $module->getTranslator();
    foreach ($tabs as $tab) {
        $tabId = (int) Tab::getInstanceFromClassName($tab['class_name'])->id;

        if (!$tabId) {
            $tabId = null;
        }

        $newTab = new Tab($tabId);
        $newTab->active = $tab['visible'];
        $newTab->class_name = $tab['class_name'];

        $newTab->route_name = $tab['route_name'];
        $newTab->name = [];
        $newTab->id_parent = Tab::getInstanceFromClassName($tab['parent_class_name'])->id;

        foreach (Language::getLanguages() as $lang) {
            $newTab->name[$lang['id_lang']] = $translator->trans($tab['name'], [], 'Modules.Psxdesign.Admin', $lang['locale']);
        }

        $newTab->module = $module->name;

        if (!$newTab->save()) {
            return false;
        }

        $newTab->position = $tab['position'];
        $newTab->save();
    }

    return true;
}

function modifyAdminThemesTab_1_1_0(PsxDesign $module): bool
{
    $adminThemesTab = Tab::getInstanceFromClassName('AdminThemes');
    $adminThemesParentTab = Tab::getInstanceFromClassName('AdminParentThemes');

    $adminThemes = new Tab($adminThemesTab->id);
    $adminThemes->active = false;
    $adminThemes->update();

    if ($adminThemesTab->id_parent !== $adminThemesParentTab->id) {
        $translator = $module->getTranslator();
        $adminThemesParent = new Tab($adminThemes->id_parent);

        foreach (Language::getLanguages() as $lang) {
            $adminThemesParent->name[$lang['id_lang']] = $translator->trans('Theme modules', [], 'Modules.Psxdesign.Admin', $lang['locale']);
        }

        $adminThemesParent->position = 1;
        $adminThemesParent->update();
    }

    return true;
}
function createColorPaletteDb_1_1_0(): void
{
    $db = Db::getInstance();

    $sqlDb = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_color_palette` (
        `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
        `name` VARCHAR(64) NOT NULL,
        `primary` VARCHAR(7) NOT NULL,
        `secondary` VARCHAR(7) NOT NULL,
        `text` VARCHAR(7) NOT NULL,
        `active` tinyint(1) DEFAULT 0 CHECK (`active` IN (0, 1)),
        `default` tinyint(1) DEFAULT 0 CHECK (`default` IN (0, 1)),
        PRIMARY KEY(`id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

    $db->execute($sqlDb);

    $sqlDefaultPalette = 'INSERT INTO ' . _DB_PREFIX_ . "psxdesign_color_palette (`name`, `primary`, `secondary`, `text`, `active`, `default`)
                            VALUES ('Default', '#24b9d7', '#f39d72', '#363a42', 0, 1);";

    $db->execute($sqlDefaultPalette);

    $sqlDefaultSecondPalette = 'INSERT INTO ' . _DB_PREFIX_ . "psxdesign_color_palette (`name`, `primary`, `secondary`, `text`, `active`, `default`)
                            VALUES ('Theme colors', '#24b9d7', '#f39d72', '#363a42', 1, 0);";

    $db->execute($sqlDefaultSecondPalette);
}
