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

namespace PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Config\ColorPlaceholderConfig;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorCategoryConfigurationData;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorConfiguration;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignColor;
use PrestaShop\Module\PsxDesign\Factory\ThemeConfiguration\ColorsConfigurationFactory;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignColorsPaletteRepository;
use PrestaShop\Module\PsxDesign\Utility\ThemeConfiguration\ColorUtility;
use Shop;

class ColorsConfigurationProvider
{
    /**
     * @var ColorUtility
     */
    private $utility;

    /**
     * @var PsxdesignColorsPaletteRepository
     */
    private $colorsPaletteRepository;

    /**
     * @var string
     */
    private $themeName;

    /**
     * @var ColorsConfigurationFactory
     */
    private $colorsFactory;

    /**
     * @var PsxDesignColorConfiguration[]|null
     */
    public $colors;

    public function __construct(
        ColorUtility $colorUtility,
        PsxdesignColorsPaletteRepository $colorsPaletteRepository,
        int $shopId
    ) {
        $this->utility = $colorUtility;
        $this->colorsPaletteRepository = $colorsPaletteRepository;
        $this->themeName = (new Shop($shopId))->theme_name;
        $this->colorsFactory = new ColorsConfigurationFactory($this->themeName);
        $this->colors = $this->colorsFactory->getColors();
    }

    /**
     * @return PsxDesignColorConfiguration[]|null
     */
    public function getColors(): ?array
    {
        return $this->colorsFactory->getColors();
    }

    /**
     * @return PsxDesignColorCategoryConfigurationData[]
     */
    public function getCurrentThemeCategorizedColorsList(): array
    {
        $activePalette = $this->colorsPaletteRepository->getActiveColorsPaletteByThemeName($this->themeName);

        if (!$activePalette) {
            return $this->getColorsCategorizedFromThemeConfigurations();
        }

        return $this->getColorsCategorizedFromThemeConfigurationsAndData($activePalette->getColors());
    }

    /**
     * @return PsxDesignColorConfiguration[]
     */
    public function getCurrentThemeColorList(): array
    {
        $activePalette = $this->colorsPaletteRepository->getActiveColorsPaletteByThemeName($this->themeName);

        if (!$activePalette) {
            return $this->colorsFactory->getColors();
        }

        return $this->utility->combineColorsEntityWithThemeConfiguration($activePalette->getColors(), $this->getColors());
    }

    /**
     * @return PsxdesignColor[]
     */
    public function getCurrentDataThemeColorList(): array
    {
        $activePalette = $this->colorsPaletteRepository->getActiveColorsPaletteByThemeName($this->themeName);

        // in case if no palette was created for the theme we return empty array
        if (!isset($activePalette)) {
            return [];
        }

        return $activePalette->getColors();
    }

    /**
     * @return bool
     */
    public function getColorFeatureAvailability(): bool
    {
        return $this->colorsFactory->getColorFeatureAvailability();
    }

    /**
     * @return PsxDesignColorCategoryConfigurationData[]
     */
    public function getColorsCategorizedPlaceholder(): array
    {
        return ColorPlaceholderConfig::getColorsPlaceholder($this->utility);
    }

    /**
     * @param PsxDesignColorConfiguration[] $previousColors
     * @param PsxdesignColor[] $colorsAfterUpdate
     *
     * @return string
     */
    public function getUpdatedColorCategoriesTitles(array $previousColors, array $colorsAfterUpdate): string
    {
        $unmatchedColors = $this->utility->getNonMatchingColors($previousColors, $colorsAfterUpdate);
        $updatedColorsTitles = [];

        foreach ($this->colorsFactory->getColorsCategories() as $category) {
            foreach ($unmatchedColors as $unmatchedColor) {
                if ($unmatchedColor->getCategory() === $category->getKey()) {
                    $updatedColorsTitles[] = $category->getTitle();
                    break;
                }
            }
        }

        $updatedColorsTitles = array_unique($updatedColorsTitles);

        return implode('|', $updatedColorsTitles);
    }

    /**
     * @return PsxdesignColor[]
     */
    public function getUnusedColorsData(): array
    {
        if (!$this->colors) {
            return [];
        }

        return $this->utility->getUnusedColorsEntityAccordingThemeConfiguration($this->getColorsDataForTheme(), $this->colors);
    }

    /**
     * @return PsxDesignColorCategoryConfigurationData[]
     */
    protected function getColorsCategorizedFromThemeConfigurations(): array
    {
        $colorsCategories = $this->colorsFactory->getColorsCategories();
        $configurationColors = $this->getColors() ?? [];

        return $this->utility->categorizeColors($colorsCategories, $configurationColors);
    }

    /**
     * @param PsxdesignColor[] $colors
     *
     * @return PsxDesignColorCategoryConfigurationData[]
     */
    protected function getColorsCategorizedFromThemeConfigurationsAndData(array $colors): array
    {
        $colorsCategories = $this->colorsFactory->getColorsCategories();
        $configurationColors = $this->getColors() ?? [];

        $colorsConfiguration = $this->utility->combineColorsEntityWithThemeConfiguration($colors, $configurationColors);

        return $this->utility->categorizeColors($colorsCategories, $colorsConfiguration);
    }

    /**
     * @return PsxdesignColor[]
     */
    private function getColorsDataForTheme(): array
    {
        $colorPalettes = $this->colorsPaletteRepository->getColorPalettesByThemeName($this->themeName);
        $colors = [];

        foreach ($colorPalettes as $palette) {
            if ($palette->isDefault()) {
                continue;
            }

            $colors = array_merge($colors, $palette->getColors());
        }

        return $colors;
    }
}
