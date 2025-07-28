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

namespace PrestaShop\Module\PsxDesign\Utility\ThemeConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Config\ColorCategoryUncategorizedConfig;
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorCategoryConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorCategoryConfigurationData;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorConfiguration;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignColor;
use PrestaShopBundle\Translation\TranslatorInterface;

class ColorUtility
{
    /**
     * @var TranslatorInterface
     */
    public $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param PsxDesignColorCategoryConfiguration[] $categories
     * @param PsxDesignColorConfiguration[] $colors
     *
     * @return PsxDesignColorCategoryConfigurationData[]
     */
    public function categorizeColors(array $categories, array $colors): array
    {
        $uncategorizedColors = $colors;
        $categorizedColors = [];

        foreach ($categories as $category) {
            $colorsForCategory = [];
            $colorsUncategorized = [];
            foreach ($uncategorizedColors as $color) {
                if ($category->getKey() === $color->getCategory()) {
                    $colorsForCategory[] = $color;
                } else {
                    $colorsUncategorized[] = $color;
                }
            }

            if (!empty($colorsForCategory)) {
                $categorizedColors[] = PsxDesignColorCategoryConfigurationData::createFromCategoryAndColors($category, $colorsForCategory);
            }
            $uncategorizedColors = $colorsUncategorized;
        }

        if (!empty($uncategorizedColors)) {
            $uncategorizedCategory = ColorCategoryUncategorizedConfig::getUncategorizedCategoryConfiguration($this->translator);
            $categorizedColors[] = PsxDesignColorCategoryConfigurationData::createFromCategoryAndColors($uncategorizedCategory, $uncategorizedColors);
        }

        return $categorizedColors;
    }

    /**
     * @param PsxdesignColor[] $colors
     * @param PsxDesignColorConfiguration[] $themeConfigurationColors
     *
     * @return PsxDesignColorConfiguration[]
     */
    public function combineColorsEntityWithThemeConfiguration(array $colors, array $themeConfigurationColors): array
    {
        $colorsConfigurationWithData = [];

        foreach ($themeConfigurationColors as $themeConfigurationColor) {
            $matchedColor = null;
            foreach ($colors as $color) {
                if ($this->compareColorDataAndColorConfiguration($color, $themeConfigurationColor)) {
                    $matchedColor = $color;
                    break;
                }
            }
            $colorsConfigurationWithData[] = PsxDesignColorConfiguration::createFromConfigurationAndEntity($themeConfigurationColor, $matchedColor);
        }

        return $colorsConfigurationWithData;
    }

    /**
     * @param PsxDesignColorConfiguration[] $colors
     * @param PsxdesignColor[] $colorsData
     *
     * @return PsxDesignColorConfiguration[]
     */
    public function getNonMatchingColors(array $colors, array $colorsData): array
    {
        $unmatchedColors = [];

        foreach ($colors as $color) {
            foreach ($colorsData as $colorData) {
                if ($color->getVariableName() === $colorData->getVariableName()
                    && $color->getValue() !== $colorData->getValue()
                ) {
                    $unmatchedColors[] = $color;
                    break;
                }
            }
        }

        return $unmatchedColors;
    }

    /**
     * @param PsxDesignColorConfiguration[] $colors
     *
     * @return array<string, array<PsxDesignColorConfiguration>>
     */
    public function groupColorsByType(array $colors): array
    {
        $groupedColors = [];

        foreach ($colors as $color) {
            switch ($color->getVariableType()) {
                case PsxDesignConfig::SCSS_VARIABLE:
                    $groupedColors[PsxDesignConfig::SCSS_VARIABLE][] = $color;
                    break;
                case PsxDesignConfig::CSS_VARIABLE:
                    $groupedColors[PsxDesignConfig::CSS_VARIABLE][] = $color;
                    break;
                case PsxDesignConfig::CSS_SELECTOR:
                    $groupedColors[PsxDesignConfig::CSS_SELECTOR][] = $color;
                    break;
            }
        }

        return $groupedColors;
    }

    /**
     * @param PsxdesignColor[] $colorsData
     * @param PsxDesignColorConfiguration[] $colorsConfiguration
     *
     * @return PsxdesignColor[]
     */
    public function getUnusedColorsEntityAccordingThemeConfiguration(array $colorsData, ?array $colorsConfiguration): array
    {
        $unusedColors = [];
        foreach ($colorsData as $colorData) {
            $foundMatchedConfiguration = false;
            foreach ($colorsConfiguration as $colorConfiguration) {
                if ($this->compareColorDataAndColorConfiguration($colorData, $colorConfiguration)) {
                    $foundMatchedConfiguration = true;
                    break;
                }
            }
            if (!$foundMatchedConfiguration) {
                $unusedColors[] = $colorData;
            }
        }

        return $unusedColors;
    }

    /**
     * @param PsxdesignColor $color
     * @param PsxDesignColorConfiguration $themeConfigurationColor
     *
     * @return bool
     */
    private function compareColorDataAndColorConfiguration(PsxdesignColor $color, PsxDesignColorConfiguration $themeConfigurationColor): bool
    {
        return $themeConfigurationColor->getVariableName() === $color->getVariableName()
            && $themeConfigurationColor->getVariableType() === $color->getVariableType();
    }
}
