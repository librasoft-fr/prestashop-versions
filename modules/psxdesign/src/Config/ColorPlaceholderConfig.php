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

namespace PrestaShop\Module\PsxDesign\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorCategoryConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorCategoryConfigurationData;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorConfiguration;
use PrestaShop\Module\PsxDesign\Utility\ThemeConfiguration\ColorUtility;

class ColorPlaceholderConfig
{
    /**
     * @param ColorUtility $colorUtility
     *
     * @return PsxDesignColorCategoryConfigurationData[]
     */
    public static function getColorsPlaceholder(ColorUtility $colorUtility): array
    {
        $colorPlaceholderConfig = new self();

        return $colorUtility->categorizeColors($colorPlaceholderConfig->getCategories(), $colorPlaceholderConfig->getColors());
    }

    /**
     * @return PsxDesignColorConfiguration[]
     */
    private function getColors(): array
    {
        $colors = [
            [
                'label' => 'Background',
                'variable_name' => '$btn-primary-bg',
                'variable_type' => 'scss_variable',
                'value' => '#24b9d7',
                'category' => 'main_button',
            ],
            [
                'label' => 'Text',
                'variable_name' => '$btn-primary-color',
                'variable_type' => 'scss_variable',
                'value' => '#ffffff',
                'category' => 'main_button',
            ],
            [
                'label' => 'Background',
                'variable_name' => '$btn-secondary-bg',
                'variable_type' => 'scss_variable',
                'value' => '#f6f6f6',
                'category' => 'secondary_button',
            ],
            [
                'label' => 'Text',
                'variable_name' => '$btn-secondary-color',
                'variable_type' => 'scss_variable',
                'value' => '#232323',
                'category' => 'secondary_button',
            ],
            [
                'label' => 'Body text',
                'variable_name' => '$gray-darker',
                'variable_type' => 'scss_variable',
                'value' => '#232323',
                'category' => 'content',
            ],
            [
                'label' => 'Text',
                'variable_name' => '$brand-primary',
                'variable_type' => 'scss_variable',
                'value' => '#24b9d7',
                'category' => 'links',
            ],
            [
                'label' => 'Background and discounted prices',
                'variable_name' => '$brand-secondary',
                'variable_type' => 'scss_variable',
                'value' => '#f39d72',
                'category' => 'discounts',
            ],
        ];

        $colorsConfiguration = [];

        foreach ($colors as $color) {
            $colorsConfiguration[] = PsxDesignColorConfiguration::createFromThemeConfiguration($color);
        }

        return $colorsConfiguration;
    }

    /**
     * @return PsxDesignColorCategoryConfiguration[]
     */
    private function getCategories(): array
    {
        $categories = [
            [
                'title' => 'Main button',
                'key' => 'main_button',
            ],
            [
                'title' => 'Secondary button',
                'key' => 'secondary_button',
            ],
            [
                'title' => 'Content',
                'key' => 'content',
            ],
            [
                'title' => 'Links',
                'key' => 'links',
            ],
            [
                'title' => 'Discounts',
                'key' => 'discounts',
            ],
        ];

        $categoriesPlaceholder = [];

        foreach ($categories as $category) {
            $categoriesPlaceholder[] = PsxDesignColorCategoryConfiguration::createFromThemeConfiguration($category);
        }

        return $categoriesPlaceholder;
    }
}
