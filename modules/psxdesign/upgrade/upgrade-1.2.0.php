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

use PrestaShop\Module\PsxDesign\Install\Tabs\Tabs;

/**
 * @param PsxDesign $module
 *
 * @return bool
 */
function upgrade_module_1_2_0(PsxDesign $module): bool
{
    try {
        $psAccountsInstaller = new PrestaShop\PsAccountsInstaller\Installer\Installer('6.0.0');
        $psAccountsInstaller->install();
    } catch (Exception $e) {
        // We do not want to break upgrade if ps accounts does not exist
    }

    return $module->registerHook('actionThemeConfiguration') &&
        $module->registerHook('actionFrontControllerSetMedia') &&
        $module->registerHook('displayAfterTitleTag') &&
        $module->registerHook('actionObjectTabUpdateAfter') &&
        $module->registerHook('displayBackOfficeHeader') &&
        installFontsTab_1_2_0($module) &&
        createFontsTable_1_2_0() &&
        renameTabs_1_2_0() &&
        renameThemeModulesTab_1_2_0();
}

function createFontsTable_1_2_0(): bool
{
    $db = Db::getInstance();

    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_fonts` (
        `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
        `css_selector` VARCHAR(64) NOT NULL,
        `font` VARCHAR(64) NOT NULL,
        `style` VARCHAR(64) NOT NULL,
        `size` INT(10) NOT NULL,
        `theme_name` VARCHAR(64) NOT NULL,
        PRIMARY KEY(`id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;';

    return $db->execute($sql);
}

function installFontsTab_1_2_0(PsxDesign $module): bool
{
    $fontTab = fontTab_1_2_0();

    $tabId = (int) Tab::getInstanceFromClassName($fontTab['class_name'])->id;

    if (!$tabId) {
        $tabId = null;
    }

    $newTab = new Tab($tabId);
    $newTab->active = $fontTab['visible'];
    $newTab->class_name = $fontTab['class_name'];

    $newTab->route_name = $fontTab['route_name'];
    $newTab->name = [];
    $newTab->id_parent = (int) Tab::getInstanceFromClassName($fontTab['parent_class_name'])->id;

    $names = $fontTab['name'];
    $languages = Language::getLanguages(false);
    foreach ($languages as $language) {
        $newTab->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
    }

    $newTab->module = $module->name;

    if (!$newTab->save()) {
        return false;
    }

    $newTab->position = $fontTab['position'];
    $newTab->save();

    return true;
}

function fontTab_1_2_0(): array
{
    return [
        'class_name' => 'AdminPsxDesignFonts',
        'visible' => true,
        'name' => [
            'en' => 'Fonts',
            'fr' => 'Polices',
            'it' => 'Fonti',
            'es' => 'Fuentes',
        ],
        'route_name' => 'admin_fonts_index',
        'parent_class_name' => 'AdminPsxDesignParentTab',
        'wording' => 'Fonts',
        'wording_domain' => 'Admin.Modules.PsxDesign',
        'position' => 3,
    ];
}

function renameTabs_1_2_0(): bool
{
    $tabs = tabsToRename_1_2_0();

    foreach ($tabs as $moduleTab) {
        $tab = Tab::getInstanceFromClassName($moduleTab['className']);

        if (!(int) $tab->id) {
            return false;
        }

        $languages = Language::getLanguages(false);
        $names = $moduleTab['name'];

        foreach ($languages as $language) {
            $tab->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
        }

        $tab->update();
    }

    return true;
}

function renameThemeModulesTab_1_2_0(): bool
{
    $adminThemesTab = Tab::getInstanceFromClassName('AdminThemes');
    $adminThemesParentTab = Tab::getInstanceFromClassName('AdminParentThemes');

    $adminThemes = new Tab($adminThemesTab->id);

    if ($adminThemesTab->id_parent !== $adminThemesParentTab->id) {
        $adminThemesParent = new Tab($adminThemes->id_parent);

        $names = Tabs::getThemeModuleTabName();

        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $adminThemesParent->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
        }

        return $adminThemesParent->update();
    }

    return true;
}

function tabsToRename_1_2_0(): array
{
    return [
        'dashboardTab' => [
            'className' => 'AdminPsxDesignParentTab',
            'name' => [
                'en' => 'Customization', // Fallback value
                'fr' => 'Personnalisation',
                'it' => 'Personalizzazione',
                'es' => 'Personalización',
            ],
        ],
        'themesTab' => [
            'className' => 'AdminPsxDesignThemeGeneral',
            'name' => [
                'en' => 'Themes',
                'fr' => 'Thèmes',
                'it' => 'Temi',
                'es' => 'Temas',
            ],
        ],
        'logosTab' => [
            'className' => 'AdminPsxDesignLogos',
            'name' => [
                'en' => 'Logos',
                'fr' => 'Logos',
                'it' => 'Logo',
                'es' => 'Logos',
            ],
        ],
        'colorsTab' => [
            'className' => 'AdminPsxDesignColors',
            'name' => [
                'en' => 'Colors',
                'fr' => 'Couleurs',
                'it' => 'Colori',
                'es' => 'Colores',
            ],
        ],
        'fontsTab' => [
            'className' => 'AdminPsxDesignFonts',
            'name' => [
                'en' => 'Fonts',
                'fr' => 'Polices',
                'it' => 'Fonti',
                'es' => 'Fuentes',
            ],
        ],
    ];
}
