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

namespace PrestaShop\Module\PsxDesign\Traits\Hooks;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Language;
use PrestaShop\Module\PsxDesign\Install\Tabs\ModuleTabsInstaller;
use PrestaShop\Module\PsxDesign\Install\Tabs\Tabs;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShopBundle\Entity\Repository\TabRepository;
use Tab;

trait UseActionAdminControllerSetMedia
{
    /**
     * @return void
     */
    public function hookActionAdminControllerSetMedia(): void
    {
        $this->checkIfTabModifyingIsRequired();

        /** @var Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');
        (int) $configuration->get(self::PSXDESIGN_MODULE_TABS_LANG_UPDATE_REQUIRED);

//         If new language is added we want to update module tabs.
        if ((int) $configuration->get(self::PSXDESIGN_MODULE_TABS_LANG_UPDATE_REQUIRED)) {
            $this->updateModuleTabsNames();

            /** @var TabRepository $tabRepository */
            $tabRepository = $this->get('prestashop.core.admin.tab.repository');

            $modifier = new ModuleTabsInstaller($tabRepository, $this->name);
            $modifier->modifyAdminThemesTab();
        }
    }

    /**
     * Function which checks version which is updated once merchant is visiting back office
     *
     * @return void
     */
    private function checkIfTabModifyingIsRequired(): void
    {
        /** @var Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');

        if (!$configuration->has(self::PSXDESIGN_MODULE_TABS_MODIFICATION_NEEDED) || $configuration->getBoolean(self::PSXDESIGN_MODULE_TABS_MODIFICATION_NEEDED)) {
            return;
        }

        /** @var TabRepository $tabRepository */
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');

        $modifier = new ModuleTabsInstaller($tabRepository, $this->name);
        $modifier->modifyAdminThemesTab();
        $configuration->set(self::PSXDESIGN_MODULE_TABS_MODIFICATION_NEEDED, '1');
    }

    /**
     * @return void
     */
    private function updateModuleTabsNames(): void
    {
        $tabs = Tabs::getTabs();

        foreach ($tabs as $tab) {
            $moduleTab = Tab::getInstanceFromClassName($tab['class_name']);

            if (!$moduleTab->id) {
                continue;
            }

            $names = $tab['name'];
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $moduleTab->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
            }

            $moduleTab->update();
        }

        /** @var Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');

        $configuration->set(self::PSXDESIGN_MODULE_TABS_LANG_UPDATE_REQUIRED, '0');
    }
}
