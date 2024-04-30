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
use PsxDesign;
use Tab;

class ModuleTabsUninstaller
{
    private const ADMIN_THEMES_TAB = 'AdminThemes';
    private const DEFAULT_ADMIN_THEMES_PARENT_TAB = 'AdminParentThemes';

    /* @var TabRepository */
    private $tabRepository;

    /* @var PsxDesign */
    private $module;

    public function __construct(TabRepository $tabRepository, PsxDesign $module)
    {
        $this->tabRepository = $tabRepository;
        $this->module = $module;
    }

    public function uninstallTabs(): bool
    {
        $tabs = Tabs::getTabs();

        foreach ($tabs as $tab) {
            $tabId = (int) $this->tabRepository->findOneIdByClassName($tab['class_name']);

            if (!$tabId) {
                continue;
            }

            $tab = new Tab($tabId);
            $tab->delete();
        }

        $this->modifyAdminThemesTab();

        return true;
    }

    /**
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function modifyAdminThemesTab()
    {
        $adminThemesTab = Tab::getInstanceFromClassName(self::ADMIN_THEMES_TAB);
        $adminThemesParentTab = Tab::getInstanceFromClassName(self::DEFAULT_ADMIN_THEMES_PARENT_TAB);

        $adminThemesTab->active = true;
        $adminThemesTab->update();

        if ((int) $adminThemesTab->id_parent !== (int) $adminThemesParentTab->id) {
            $translator = $this->module->getTranslator();
            $notDefaultParentTab = new Tab((int) $adminThemesTab->id_parent);

            foreach (Language::getLanguages() as $lang) {
                $notDefaultParentTab->name[$lang['id_lang']] = $translator->trans('Theme & Logo', [], 'Modules.Psxdesign.Admin', $lang['locale']);
            }

            $notDefaultParentTab->position = 0;
            $notDefaultParentTab->update();
        }
    }
}
