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

use PrestaShop\Module\PsxDesign\Config\FontCategoryUncategorizedConfig;
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontCategoryConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontCategoryConfigurationData;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignFonts;
use PrestaShopBundle\Translation\TranslatorInterface;

class FontUtility
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
     * @param PsxDesignFontConfiguration[] $fonts
     * @param PsxDesignFontCategoryConfiguration[] $categories
     *
     * @return PsxDesignFontCategoryConfigurationData[]
     */
    public function categorizeFonts(array $fonts, array $categories): array
    {
        $uncategorizedFonts = $fonts;
        $categorizedFonts = [];

        foreach ($categories as $category) {
            $fontsForCategory = [];
            $fontsUncategorized = [];
            foreach ($uncategorizedFonts as $font) {
                if ($category->getKey() === $font->getCategory()) {
                    $fontsForCategory[] = $font;
                } else {
                    $fontsUncategorized[] = $font;
                }
            }

            if (!empty($fontsForCategory)) {
                $categorizedFonts[] = PsxDesignFontCategoryConfigurationData::createFromCategoryAndFonts($category, $fontsForCategory);
            }
            $uncategorizedFonts = $fontsUncategorized;
        }

        if (!empty($uncategorizedFonts)) {
            $uncategorizedCategory = FontCategoryUncategorizedConfig::getUncategorizedCategoryConfiguration($this->translator);
            $categorizedFonts[] = PsxDesignFontCategoryConfigurationData::createFromCategoryAndFonts($uncategorizedCategory, $uncategorizedFonts);
        }

        return $categorizedFonts;
    }

    /**
     * @param PsxdesignFonts[] $fontsData
     * @param PsxDesignFontConfiguration[] $fontsConfiguration
     *
     * @return PsxDesignFontConfiguration[]
     */
    public function combineFontsEntityWithThemeConfiguration(array $fontsData, array $fontsConfiguration): array
    {
        $fontsConfigurationWithData = [];

        foreach ($fontsConfiguration as $fontConfiguration) {
            $dataCorrespondsConfiguration = null;
            foreach ($fontsData as $fontData) {
                if ($this->compareFontDataFontConfiguration($fontData, $fontConfiguration)) {
                    $dataCorrespondsConfiguration = $fontData;
                    break;
                }
            }
            $fontsConfigurationWithData[] = PsxDesignFontConfiguration::createFromFontConfigurationAndEntity($fontConfiguration, $dataCorrespondsConfiguration);
        }

        return $fontsConfigurationWithData;
    }

    /**
     * @param PsxdesignFonts[] $fontsData
     * @param PsxDesignFontConfiguration[] $fontsConfiguration
     *
     * @return PsxdesignFonts[]
     */
    public function getUnusedFontsEntityAccordingThemeConfiguration(array $fontsData, array $fontsConfiguration): array
    {
        $unusedFonts = [];
        foreach ($fontsData as $fontData) {
            $foundMatchedConfiguration = false;
            foreach ($fontsConfiguration as $fontConfiguration) {
                if ($this->compareFontDataFontConfiguration($fontData, $fontConfiguration)) {
                    $foundMatchedConfiguration = true;
                    break;
                }
            }
            if (!$foundMatchedConfiguration) {
                $unusedFonts[] = $fontData;
            }
        }

        return $unusedFonts;
    }

    /**
     * @param PsxDesignFontConfiguration[] $fontsData1
     * @param PsxDesignFontConfiguration[] $fontsData2
     *
     * @return PsxDesignFontConfiguration[]
     */
    public function getUniqueFontConfigurationDataFromArrays(array $fontsData1, array $fontsData2): array
    {
        // Initialize an empty array to hold unique font configuration data
        $uniqueFontConfigurationData = [];

        foreach ($fontsData1 as $fontData1) {
            // Assume the font data is unique by default
            $isUnique = true;
            foreach ($fontsData2 as $fontData2) {
                /* If the CSS selector of the current element in the first array
                is the same as the CSS selector of the current element in the second array,
                then the font data is not unique */
                if ($fontData1->getVariableName() === $fontData2->getVariableName()) {
                    $isUnique = false;
                }
            }

            // If the font data is unique, add it to the array of unique font configuration data
            if ($isUnique) {
                $uniqueFontConfigurationData[] = $fontData1;
            }
        }

        return $uniqueFontConfigurationData;
    }

    /**
     * @param PsxDesignFontConfiguration[] $fonts
     * @param PsxdesignFonts[] $fontsData
     *
     * @return PsxDesignFontConfiguration[]
     */
    public function getNonMatchingFonts(array $fonts, array $fontsData): array
    {
        $unmatchedFonts = [];

        foreach ($fonts as $font) {
            foreach ($fontsData as $fontData) {
                if ($font->getVariableName() === $fontData->getVariableName() &&
                    (
                        $font->getFont() !== $fontData->getFont() ||
                        $font->getStyle() !== $fontData->getStyle() ||
                        $font->getSize() !== $fontData->getSize()
                    )
                ) {
                    $unmatchedFonts[] = $font;
                    break;
                }
            }
        }

        return $unmatchedFonts;
    }

    /**
     * @param PsxDesignFontConfiguration[] $fontsConfiguration
     * @param PsxdesignFonts[] $fontsData
     *
     * @return PsxDesignFontConfiguration[]
     */
    public function getUsedConfigurationsAccordingFontsData(array $fontsConfiguration, array $fontsData): array
    {
        $matchedFonts = [];

        foreach ($fontsConfiguration as $fontConfiguration) {
            foreach ($fontsData as $fontData) {
                if ($this->compareFontDataFontConfiguration($fontData, $fontConfiguration)) {
                    $matchedFonts[] = PsxDesignFontConfiguration::createFromFontConfigurationAndEntity($fontConfiguration, $fontData);
                    break;
                }
            }
        }

        return $matchedFonts;
    }

    /**
     * @param PsxDesignFontConfiguration[] $fonts
     *
     * @return array<string, array<PsxDesignFontConfiguration>>
     */
    public function groupFontsByType(array $fonts): array
    {
        $groupedFonts = [];

        foreach ($fonts as $font) {
            switch ($font->getVariableType()) {
                case PsxDesignConfig::CSS_VARIABLE:
                    $groupedFonts[PsxDesignConfig::CSS_VARIABLE][] = $font;
                    break;
                case PsxDesignConfig::CSS_SELECTOR:
                    $groupedFonts[PsxDesignConfig::CSS_SELECTOR][] = $font;
                    break;
            }
        }

        return $groupedFonts;
    }

    /**
     * @param PsxdesignFonts $fontsData
     * @param PsxDesignFontConfiguration $fontConfiguration
     *
     * @return bool
     */
    public function compareFontDataFontConfiguration(PsxdesignFonts $fontsData, PsxDesignFontConfiguration $fontConfiguration): bool
    {
        return $fontsData->getVariableName() === $fontConfiguration->getVariableName()
            && $fontsData->getVariableType() === $fontConfiguration->getVariableType();
    }
}
