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

use PrestaShop\Module\PsxDesign\Config\FontPlaceholderConfig;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontCategoryConfigurationData;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignFonts;
use PrestaShop\Module\PsxDesign\Factory\ThemeConfiguration\FontsConfigurationFactory;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignFontsRepository;
use PrestaShop\Module\PsxDesign\Utility\ThemeConfiguration\FontUtility;
use Shop;

class FontsConfigurationProvider
{
    /**
     * @var PsxdesignFontsRepository
     */
    private $fontsRepository;

    /**
     * @var FontUtility
     */
    private $utility;

    /**
     * @var string
     */
    private $themeName;

    /**
     * @var FontsConfigurationFactory
     */
    private $fontsFactory;

    /**
     * @var PsxDesignFontConfiguration[]|null
     */
    public $fonts;

    public function __construct(
        PsxdesignFontsRepository $fontsRepository,
        FontUtility $utility,
        int $shopId
    ) {
        $this->fontsRepository = $fontsRepository;
        $this->utility = $utility;
        $this->themeName = (new Shop($shopId))->theme_name;
        $this->fontsFactory = new FontsConfigurationFactory($this->themeName);
        $this->fonts = $this->fontsFactory->getFonts();
    }

    /**
     * @return PsxDesignFontConfiguration[]|null
     */
    public function getFonts(): ?array
    {
        return $this->fonts;
    }

    /**
     * @return bool
     */
    public function getFontFeatureAvailability(): bool
    {
        return $this->fontsFactory->provideFontFeatureAvailability();
    }

    /**
     * @return PsxdesignFonts[]
     */
    public function getUnusedFontsData(): array
    {
        if (!$this->fonts) {
            return [];
        }

        return $this->utility->getUnusedFontsEntityAccordingThemeConfiguration($this->getFontsDataForTheme(), $this->fonts);
    }

    /**
     * @return PsxDesignFontConfiguration[]
     */
    public function getFontsFromThemeConfigurationAndData(): array
    {
        if (!$this->fonts) {
            return [];
        }

        return $this->utility->combineFontsEntityWithThemeConfiguration($this->getFontsDataForTheme(), $this->fonts);
    }

    /**
     * @return PsxDesignFontCategoryConfigurationData[]
     */
    public function getFontsCategorizedFromThemeConfigurationAndData(): array
    {
        if (!$this->fonts) {
            return [];
        }

        return $this->utility->categorizeFonts($this->getFontsFromThemeConfigurationAndData(), $this->fontsFactory->getFontCategories());
    }

    /**
     * @return PsxDesignFontCategoryConfigurationData[]
     */
    public function getFontsCategorizedPlaceholder(): array
    {
        return FontPlaceholderConfig::getFontsPlaceholder($this->utility);
    }

    /**
     * @param PsxDesignFontConfiguration[] $previousFonts
     * @param PsxdesignFonts[] $fontsAfterUpdate
     *
     * @return string
     */
    public function getUpdatedFontsCategoriesTitles(array $previousFonts, array $fontsAfterUpdate): string
    {
        $unmatchedFonts = $this->utility->getNonMatchingFonts($previousFonts, $fontsAfterUpdate);
        $updatedFontsCategoryTitles = [];

        foreach ($this->fontsFactory->getFontCategories() as $category) {
            foreach ($unmatchedFonts as $unmatchedFont) {
                if ($unmatchedFont->getCategory() === $category->getKey()) {
                    $updatedFontsCategoryTitles[] = $category->getTitle();
                }
            }
        }

        $updatedFontsCategoryTitles = array_unique($updatedFontsCategoryTitles);

        return implode('|', $updatedFontsCategoryTitles);
    }

    /**
     * @return PsxdesignFonts[]
     */
    private function getFontsDataForTheme(): array
    {
        return $this->fontsRepository->getFontsByThemeName($this->themeName);
    }
}
