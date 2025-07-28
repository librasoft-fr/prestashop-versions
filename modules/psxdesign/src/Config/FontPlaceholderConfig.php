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

use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontCategoryConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontCategoryConfigurationData;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;
use PrestaShop\Module\PsxDesign\Utility\ThemeConfiguration\FontUtility;

class FontPlaceholderConfig
{
    /**
     * @param FontUtility $fontUtility
     *
     * @return PsxDesignFontCategoryConfigurationData[]
     */
    public static function getFontsPlaceholder(FontUtility $fontUtility): array
    {
        $fontPlaceholderConfig = new self();

        return $fontUtility->categorizeFonts($fontPlaceholderConfig->getFonts(), $fontPlaceholderConfig->getCategories());
    }

    /**
     * @return PsxDesignFontConfiguration[]
     */
    private function getFonts(): array
    {
        $fonts = [
            [
                'label' => 'H1',
                'variable_name' => 'h1, .h1',
                'variable_type' => 'css_selector',
                'font' => 'Manrope',
                'style' => 'normal-700',
                'size' => 22,
                'description' => 'The Header 1 (H1) is the main title of the page.',
                'placeholder' => 'Main title',
                'category' => 'headings',
            ],
            [
                'label' => 'Paragraph',
                'variable_name' => 'p',
                'variable_type' => 'css_selector',
                'font' => 'Manrope',
                'style' => 'normal-400',
                'size' => 14,
                'description' => 'The Paragraph (P) is the main paragraph, the important text of the page.',
                'placeholder' => 'Main paragraph',
                'category' => 'paragraphs',
            ],
        ];

        $fontsConfiguration = [];

        foreach ($fonts as $font) {
            $fontsConfiguration[] = PsxDesignFontConfiguration::createFromThemeConfiguration($font);
        }

        return $fontsConfiguration;
    }

    /**
     * @return PsxDesignFontCategoryConfiguration[]
     */
    private function getCategories(): array
    {
        $categories = [
            [
                'title' => 'Headings',
                'description' => 'The titles in the page have a very important role in the optimization of natural referencing.',
                'key' => 'headings',
            ],
            [
                'title' => 'Paragraphs',
                'description' => 'The paragraphs correspond to the content of your pages.',
                'key' => 'paragraphs',
            ],
        ];

        $categoriesPlaceholder = [];

        foreach ($categories as $category) {
            $categoriesPlaceholder[] = PsxDesignFontCategoryConfiguration::createFromThemeConfiguration($category);
        }

        return $categoriesPlaceholder;
    }
}
