<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsEditionBasic\Traits\Hooks;

use PrestaShop\Module\PsEditionBasic\Install\Tabs\Tabs;
use PrestaShop\PrestaShop\Adapter\Configuration;

trait UseActionAdminControllerSetMedia
{
    /**
     * @return void
     */
    public function hookActionAdminControllerSetMedia(): void
    {
        /** @var Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');
        (int) $configuration->get(self::PS_EDITION_BASIC_MODULE_TABS_LANG_UPDATE_REQUIRED);

        // If new language is added we want to update module tabs.
        // dd((int) $configuration->get(self::PS_EDITION_BASIC_MODULE_TABS_LANG_UPDATE_REQUIRED));
        if ((int) $configuration->get(self::PS_EDITION_BASIC_MODULE_TABS_LANG_UPDATE_REQUIRED)) {
            $this->updateModuleTabsNames();
        }

        // Loading the reskin because this hook is call inside iframes
        $this->context->controller->addCSS($this->getParameter('ps_edition_basic.edition_basic_admin_css'));
    }

    /**
     * @return void
     */
    private function updateModuleTabsNames(): void
    {
        $tabs = Tabs::getTabs();

        foreach ($tabs as $tab) {
            $moduleTab = \Tab::getInstanceFromClassName($tab['class_name']);

            if (!$moduleTab->id) {
                continue;
            }

            $names = $tab['name'];
            $languages = \Language::getLanguages(false);
            foreach ($languages as $language) {
                $moduleTab->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
            }

            $moduleTab->update();
        }

        /** @var Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');

        $configuration->set(self::PS_EDITION_BASIC_MODULE_TABS_LANG_UPDATE_REQUIRED, '0');
    }
}
