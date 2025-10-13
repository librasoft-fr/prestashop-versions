<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsClassicEdition\Install\Tabs;

use Symfony\Contracts\Translation\TranslatorInterface;
use Tab;

class TabsInstaller
{
    public function __construct(
        private string $moduleName,
        private TranslatorInterface $translator,
    ) {
    }

    public function run(): bool
    {
        return $this->installTabs();
    }

    public function installTabs(): bool
    {
        $result = true;

        $tabs = Tabs::getTabs();
        foreach ($tabs as $tabItem) {
            $tabId = \Tab::getIdFromClassName($tabItem['class_name']);

            if (!$tabId) {
                $tabId = null;
            }

            $tab = new \Tab($tabId);
            $tab->id_parent = $tabItem['parent'] ? \Tab::getIdFromClassName($tabItem['parent']) : 0;
            $tab->class_name = $tabItem['class_name'];
            $tab->route_name = $tabItem['route_name'];
            $tab->icon = $tabItem['icon'];
            $tab->active = $tabItem['active'];
            $tab->enabled = $tabItem['enabled'];
            $tab->module = $this->moduleName;
            $tab->wording = $tabItem['wording'];
            $tab->wording_domain = $tabItem['wording_domain'];
            $tab->name = [];

            $languages = \Language::getLanguages(false);
            foreach ($languages as $language) {
                $tab->name[(int) $language['id_lang']] = $this->translator->trans($tabItem['wording'], [], $tabItem['wording_domain'], $language['locale']);
            }

            $result = $result && $tab->save();

            $tab->updatePosition(false, $tabItem['position']);
        }

        // Update homepage tab position
        $tab = new \Tab(\Tab::getIdFromClassName('AdminDashboard'));
        $tab->id_parent = \Tab::getIdFromClassName('HOME');
        $tab->save();
        $tab->updatePosition(false, 0);

        return $result;
    }
}
