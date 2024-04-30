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

trait UseActionAdminBreadcrumbModifier
{
    /**
     * Hook actionAdminBreadcrumbModifier.
     * Modify breadcrumb in BackOffice to add Parametres in children of Configure
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionAdminBreadcrumbModifier(array &$params)
    {
        if (!\Configuration::get('SMB_IS_NEW_MENU_ENABLED')) {
            return;
        }

        $tabs = $params['tabs'];
        $breadcrumb = $params['breadcrumb'];
        // return if tabs is an array containing false
        if (in_array(false, $tabs)) {
            return;
        }

        // remove href in breadcrumb if href contains ?controller=
        foreach ($breadcrumb as &$item) {
            if (isset($item['href']) && strpos($item['href'], '?controller=') !== false) {
                $item['href'] = null;
            }
        }

        foreach ($tabs as $tab) {
            if (in_array($tab['class_name'], PS_EDITION_BASIC_SETTINGS_WHITE_LIST)) {
                array_unshift($breadcrumb, [
                    'href' => $this->context->link->getAdminLink('CONFIGURE'),
                    'name' => 'Paramètres',
                ]);
            }
        }

        $params['breadcrumb'] = $breadcrumb;
    }
}
