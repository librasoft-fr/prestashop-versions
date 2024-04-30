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

declare(strict_types=1);

namespace PrestaShop\Module\PsxDesign\Install\Tabs;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Language;
use PrestaShopBundle\Entity\Repository\TabRepository;
use Tab;

class ModuleTabsInstaller
{
    private const ADMIN_THEMES_TAB = 'AdminThemes';
    private const DEFAULT_ADMIN_THEMES_PARENT_TAB = 'AdminParentThemes';

    /* @var TabRepository */
    private $tabRepository;

    /* @var string */
    private $moduleName;

    public function __construct(TabRepository $tabRepository, string $moduleName)
    {
        $this->tabRepository = $tabRepository;
        $this->moduleName = $moduleName;
    }

    public function installTabs(): bool
    {
        $tabs = Tabs::getTabs();

        foreach ($tabs as $tab) {
            $tabId = $this->tabRepository->findOneIdByClassName($tab['class_name']);

            if (!$tabId) {
                $tabId = null;
            }

            $newTab = new Tab($tabId);
            $newTab->active = $tab['visible'];
            $newTab->class_name = $tab['class_name'];

            $newTab->route_name = $tab['route_name'];
            $newTab->name = [];
            $newTab->id_parent = $this->tabRepository->findOneIdByClassName($tab['parent_class_name']);

            $newTab->module = $this->moduleName;

            $names = $tab['name'];
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $newTab->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
            }

            if (!$newTab->save()) {
                return false;
            }

            $newTab->position = $tab['position'];
            $newTab->save();
        }

        $this->modifyAdminThemesTab();

        return true;
    }

    /**
     * @return void
     */
    public function modifyAdminThemesTab(): void
    {
        $adminThemesTab = Tab::getInstanceFromClassName(self::ADMIN_THEMES_TAB);
        $adminThemesParentTab = Tab::getInstanceFromClassName(self::DEFAULT_ADMIN_THEMES_PARENT_TAB);

        $adminThemes = new Tab($adminThemesTab->id);
        $adminThemes->active = false;
        $adminThemes->update();

        //if parent is not default we rename it.

        if ((int) $adminThemes->id_parent !== (int) $adminThemesParentTab->id) {
            $adminThemesParent = new Tab($adminThemes->id_parent);

            $names = Tabs::getThemeModuleTabName();

            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                $adminThemesParent->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
            }

            $adminThemesParent->update();
        }
    }
}
