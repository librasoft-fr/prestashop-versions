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

namespace PrestaShop\Module\PsEditionBasic\Actions;

class Uninstall
{
    /**
     * @var string
     */
    private $moduleName;

    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
    }

    public function run(): bool
    {
        return $this->uninstallTabs();
    }

    public function uninstallTabs(): bool
    {
        $result = true;

        foreach (['HOME', 'AdminPsEditionBasicHomepageController', 'AdminPsEditionBasicSettingsController'] as $tabItemClassName) {
            $id_tab = (int) \Tab::getIdFromClassName($tabItemClassName);

            $tab = new \Tab($id_tab);
            if (\Validate::isLoadedObject($tab) && $tab->module === $this->moduleName) {
                $result = $result && $tab->delete();
            }
        }

        return $result;
    }
}
