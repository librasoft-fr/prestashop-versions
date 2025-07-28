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

use PrestaShopBundle\Entity\Repository\TabRepository;

/**
 * @param PsxDesign $module
 *
 * @return bool
 */
function upgrade_module_1_0_0(PsxDesign $module): bool
{
    $tabs = getTabs_1_0_0();

    /** @var TabRepository $tabRepository */
    $tabRepository = $module->get('prestashop.core.admin.tab.repository');

    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psxdesign_logo` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `logo_destination` enum("header", "email", "invoice") NOT NULL,
            `logo_type` enum("image", "text") NOT NULL,
            `text` VARCHAR(64),
            `size` VARCHAR(64),
            `font` VARCHAR(64),
            `use_header_logo` tinyint(1),
            `font_size` INT(10),
            `color` VARCHAR(64),
            `style` VARCHAR(64),
            `active` tinyint(1),
            PRIMARY KEY (`id`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

    return Db::getInstance()->execute($sql)
        && installTabs_1_0_0($tabs, $tabRepository, $module)
        && renameTabs_1_0_0($module, $tabRepository)
        && changeTabPosition_1_0_0($tabRepository);
}

/**
 * Get module tabs information for installation
 *
 * @return array<int, array<string, mixed>>
 */
function getTabs_1_0_0(): array
{
    return [
        [
            'class_name' => 'AdminPsxDesignLogos',
            'visible' => true,
            'name' => 'Logos',
            'route_name' => 'admin_logos_index',
            'parent_class_name' => 'AdminThemesParent',
            'wording' => 'Logos',
            'wording_domain' => 'Admin.Modules.PsxDesign',
        ],
    ];
}

/**
 * List of tabs that has to be renamed
 *
 * @return array<string, array{name: string, className: string}>
 */
function getTabsToRename_1_0_0(): array
{
    return [
        'sidebarMainTab' => [
            'name' => 'General',
            'className' => 'AdminThemesParent',
        ],
        'generalTab' => [
            'name' => 'Themes',
            'className' => 'AdminThemes',
        ],
    ];
}

function installTabs_1_0_0(array $tabs, TabRepository $tabRepository, PsxDesign $module): bool
{
    $translator = $module->getTranslator();

    foreach ($tabs as $tab) {
        $tabId = $tabRepository->findOneIdByClassName($tab['class_name']);

        if (!$tabId) {
            $tabId = null;
        }

        $newTab = new Tab($tabId);
        $newTab->active = $tab['visible'];
        $newTab->class_name = $tab['class_name'];

        $newTab->route_name = $tab['route_name'];
        $newTab->name = [];
        $newTab->id_parent = $tabRepository->findOneIdByClassName($tab['parent_class_name']);

        foreach (Language::getLanguages() as $lang) {
            $newTab->name[$lang['id_lang']] = $translator->trans($tab['name'], [], 'Modules.Psxdesign.Admin', $lang['locale']);
        }

        $newTab->module = $module->name;

        if (!$newTab->save()) {
            return false;
        }
    }

    return true;
}

function renameTabs_1_0_0(PsxDesign $module, TabRepository $tabRepository): bool
{
    $translator = $module->getTranslator();
    $tabs = getTabsToRename_1_0_0();

    foreach ($tabs as $tab) {
        $tabId = $tabRepository->findOneIdByClassName($tab['className']);
        $psTab = new Tab($tabId);

        if (!$psTab->id) {
            return false;
        }

        foreach (Language::getLanguages() as $lang) {
            $psTab->name[$lang['id_lang']] = $translator->trans($tab['name'], [], 'Modules.Psxdesign.Admin', $lang['locale']);
        }

        $psTab->update();
    }

    return true;
}

/**
 * Changing tabs position to provide easier accessibility and visibility to the configurations page
 *
 * @return bool
 */
function changeTabPosition_1_0_0(TabRepository $tabRepository): bool
{
    $tabId = $tabRepository->findOneIdByClassName('AdminPsxDesignLogos');
    $tab = new Tab($tabId);

    if (!$tab->id) {
        return false;
    }

    $tab->updatePosition(false, 2);

    return true;
}
