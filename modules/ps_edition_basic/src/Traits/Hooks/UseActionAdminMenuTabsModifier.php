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

trait UseActionAdminMenuTabsModifier
{
    /**
     * Hook actionAdminMenuTabsModifier.
     * Modify tabs in BackOffice
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionAdminMenuTabsModifier(array &$params): void
    {
        if (!\Configuration::get('SMB_IS_NEW_MENU_ENABLED')) {
            return;
        }

        $navItems = $params['tabs'];

        if (in_array('SELL', array_column($navItems, 'class_name'))) {
            $navItems = $this->flatSecondLevelRemovingFirstLevel($navItems);
        }
        $navItems = $this->pickTabs($navItems, PS_EDITION_BASIC_MENU_WHITE_LIST);
        $navItems = $this->removeUnactivatedSubTabs($navItems);

        $this->context->smarty->assign('tabs_simplify', $navItems);
    }

    private function flatSecondLevelRemovingFirstLevel(array $tabs): array
    {
        $result = [];

        foreach ($tabs as $tab) {
            if (isset($tab['sub_tabs']) && is_array($tab['sub_tabs'])) {
                foreach ($tab['sub_tabs'] as $subTab) {
                    array_push($result, $subTab);
                }
            }
        }

        return $result;
    }

    private function pickTabs(array $tabs, array $whiteListTabs): array
    {
        $result = [];
        foreach ($tabs as $tab) {
            if (in_array($tab['class_name'], $whiteListTabs)) {
                $result[] = $tab;
            }
        }

        return $result;
    }

    private function removeUnactivatedSubTabs(array $tabs): array
    {
        $result = $tabs;

        foreach ($result as $key => $tab) {
            foreach ($tab['sub_tabs'] as $subKey => $subTab) {
                if (isset($subTab['active']) && $subTab['active'] == 0) {
                    unset($result[$key]['sub_tabs'][$subKey]);
                }
            }
        }

        return $result;
    }
}
