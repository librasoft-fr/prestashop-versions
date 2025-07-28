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

class Tabs
{
    private const ADMIN_PARENT_THEMES_PS_TAB = 'AdminParentThemes';
    private const MODULE_PARENT_TAB = 'AdminPsxDesignParentTab';

    /**
     * Get module tabs information for installation
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getTabs(): array
    {
        return [
            [
                'class_name' => self::MODULE_PARENT_TAB,
                'visible' => true,
                'name' => [
                    'en' => 'Customization', // Fallback value
                    'fr' => 'Personnalisation',
                    'it' => 'Personalizzazione',
                    'es' => 'Personalización',
                ],
                'route_name' => '',
                'parent_class_name' => self::ADMIN_PARENT_THEMES_PS_TAB,
                'wording' => 'Customization',
                'wording_domain' => 'Admin.Modules.PsxDesign',
                'position' => 0,
            ],
            [
                'class_name' => 'AdminPsxDesignThemeGeneral',
                'visible' => true,
                'name' => [
                    'en' => 'Themes', // Fallback value
                    'fr' => 'Thèmes',
                    'it' => 'Temi',
                    'es' => 'Temas',
                ],
                'route_name' => 'admin_psxdesign_themes_index',
                'parent_class_name' => self::MODULE_PARENT_TAB,
                'wording' => 'Themes',
                'wording_domain' => 'Admin.Modules.PsxDesign',
                'position' => 0,
            ],
            [
                'class_name' => 'AdminPsxDesignLogos',
                'visible' => true,
                'name' => [
                    'en' => 'Logos', // Fallback value
                    'fr' => 'Logos',
                    'it' => 'Logo',
                    'es' => 'Logos',
                ],
                'route_name' => 'admin_logos_index',
                'parent_class_name' => self::MODULE_PARENT_TAB,
                'wording' => 'Logos',
                'wording_domain' => 'Admin.Modules.PsxDesign',
                'position' => 1,
            ],
            [
                'class_name' => 'AdminPsxDesignColors',
                'visible' => true,
                'name' => [
                    'en' => 'Colors', // Fallback value
                    'fr' => 'Couleurs',
                    'it' => 'Colori',
                    'es' => 'Colores',
                ],
                'route_name' => 'admin_colors_index',
                'parent_class_name' => self::MODULE_PARENT_TAB,
                'wording' => 'Colors',
                'wording_domain' => 'Admin.Modules.PsxDesign',
                'position' => 2,
            ],
            [
                'class_name' => 'AdminPsxDesignFonts',
                'visible' => true,
                'name' => [
                    'en' => 'Fonts', // Fallback value
                    'fr' => 'Polices',
                    'it' => 'Fonti',
                    'es' => 'Fuentes',
                ],
                'route_name' => 'admin_fonts_index',
                'parent_class_name' => self::MODULE_PARENT_TAB,
                'wording' => 'Fonts',
                'wording_domain' => 'Admin.Modules.PsxDesign',
                'position' => 3,
            ],
        ];
    }

    /**
     * @return string[]
     */
    public static function getThemeModuleTabName(): array
    {
        return [
            'en' => 'Theme modules',
            'fr' => 'Modules du thème',
            'it' => 'Moduli tema',
            'es' => 'Modulos del tema',
        ];
    }
}
