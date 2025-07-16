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

namespace PrestaShop\Module\PsClassicEdition\Install\Tabs;

class Tabs
{
    /**
     * Get module tabs information for installation
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getTabs(): array
    {
        return [
            [
                'class_name' => 'HOME',
                'route_name' => '',
                'icon' => '',
                'parent' => '',
                'position' => 0,
                'active' => true,
                'enabled' => true,
                'wording' => 'Welcome',
                'wording_domain' => 'Modules.Classicedition.Admin',
            ],
            [
                'class_name' => 'AdminPsClassicEditionHomepageController',
                'route_name' => 'ps_classic_edition_homepage',
                'icon' => 'support_agent',
                'parent' => 'HOME',
                'position' => 0,
                'active' => true,
                'enabled' => true,
                'wording' => 'Care Center',
                'wording_domain' => 'Modules.Classicedition.Admin',
            ],
        ];
    }
}
