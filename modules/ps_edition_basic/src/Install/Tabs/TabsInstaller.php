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

namespace PrestaShop\Module\PsEditionBasic\Install\Tabs;

class TabsInstaller
{
    /**
     * @var string
     */
    private $moduleName;

    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
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
            $tab->name = [];

            $names = $tabItem['name'];

            $languages = \Language::getLanguages(false);
            foreach ($languages as $language) {
                $tab->name[(int) $language['id_lang']] = $names[$language['iso_code']] ?? $names['en'];
            }

            $result = $result && $tab->save();

            $tab->updatePosition(false, $tabItem['position']);
        }

        // Update homepage tab position
        $tab = new \Tab(\Tab::getIdFromClassName('AdminDashboard'));
        $tab->id_parent = \Tab::getIdFromClassName('HOME');
        $tab->save();
        $tab->updatePosition(false, 1);

        // Update employees default tab
        $tab = new \Tab(\Tab::getIdFromClassName('AdminPsEditionBasicHomepageController'));
        \Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . "employee SET default_tab = '$tab->id';");

        return $result;
    }
}
