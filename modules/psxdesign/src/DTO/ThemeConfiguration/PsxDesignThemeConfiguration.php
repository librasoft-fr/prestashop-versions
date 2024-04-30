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

namespace PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorCategoryConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontCategoryConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;
use Symfony\Component\HttpFoundation\ParameterBag;

class PsxDesignThemeConfiguration
{
    /**
     * @var string
     */
    private $theme;

    /**
     * @var string
     */
    private $customizeUrl;

    /**
     * @var string[]
     */
    private $scssFiles;

    /**
     * @var string
     */
    private $scssBaseFolder;

    /**
     * @var PsxDesignFontConfiguration[]
     */
    private $fonts;

    /**
     * @var PsxDesignColorConfiguration[]
     */
    private $colors;

    /**
     * @var PsxDesignFontCategoryConfiguration[]
     */
    private $fontsCategories;

    /**
     * @var array
     */
    private $colorsCategories;

    private function __construct(ParameterBag $data)
    {
        $this->setTheme($data->get('theme') ?? '');
        $this->setCustomizeUrl($data->get('customize_url') ?? '');
        $this->setScssFiles($data->get('scss_files') ?? []);
        $this->setScssBaseFolder($data->get('scss_base_folder') ?? '');
        $this->setFonts($data->get('fonts') ?? []);
        $this->setColors($data->get('colors') ?? []);
        $this->setFontsCategories($data->get('fonts_categories') ?? []);
        $this->setColorsCategories($data->get('colors_categories') ?? []);
    }

    /**
     * @param array $themeConfiguration
     *
     * @return PsxDesignThemeConfiguration
     */
    public static function createFromThemeConfiguration(array $themeConfiguration): self
    {
        $parameterBag = new ParameterBag($themeConfiguration);

        return new self($parameterBag);
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @return string
     */
    public function getCustomizeUrl(): ?string
    {
        return $this->customizeUrl;
    }

    /**
     * @return string[]|null
     */
    public function getScssFiles(): ?array
    {
        return $this->scssFiles;
    }

    /**
     * @return string|null
     */
    public function getScssBaseFolder(): ?string
    {
        return $this->scssBaseFolder;
    }

    /**
     * @return PsxDesignFontConfiguration[]|null
     */
    public function getFonts(): ?array
    {
        return $this->fonts;
    }

    /**
     * @return PsxDesignColorConfiguration[]|null
     */
    public function getColors(): ?array
    {
        return $this->colors;
    }

    /**
     * @return PsxDesignFontCategoryConfiguration[]|null
     */
    public function getFontsCategories(): ?array
    {
        return $this->fontsCategories;
    }

    /**
     * @return PsxDesignColorCategoryConfiguration[]|null
     */
    public function getColorsCategories(): ?array
    {
        return $this->colorsCategories;
    }

    /**
     * @param string $theme
     *
     * @return void
     */
    private function setTheme(string $theme): void
    {
        $this->theme = trim($theme);
    }

    /**
     * @param string $customizeUrl
     *
     * @return void
     */
    private function setCustomizeUrl(string $customizeUrl): void
    {
        $this->customizeUrl = trim($customizeUrl);
    }

    /**
     * @param array $scssFiles
     *
     * @return void
     */
    private function setScssFiles(array $scssFiles): void
    {
        $this->scssFiles = $scssFiles;
    }

    /**
     * @param string|null $scssBaseFolder
     *
     * @return void
     */
    private function setScssBaseFolder(?string $scssBaseFolder): void
    {
        $this->scssBaseFolder = trim($scssBaseFolder);
    }

    /**
     * @param array $fonts
     *
     * @return void
     */
    private function setFonts(array $fonts): void
    {
        $fontsDto = [];
        foreach ($fonts as $font) {
            $fontsDto[] = PsxDesignFontConfiguration::createFromThemeConfiguration($font);
        }
        $this->fonts = $fontsDto;
    }

    /**
     * @param array $colors
     *
     * @return void
     */
    private function setColors(array $colors): void
    {
        $colorsDto = [];
        foreach ($colors as $color) {
            $colorsDto[] = PsxDesignColorConfiguration::createFromThemeConfiguration($color);
        }

        $this->colors = $colorsDto;
    }

    /**
     * @param array $fontsCategories
     *
     * @return void
     */
    private function setFontsCategories(array $fontsCategories): void
    {
        $fontsCategoriesDto = [];
        foreach ($fontsCategories as $fontCategory) {
            $fontsCategoriesDto[] = PsxDesignFontCategoryConfiguration::createFromThemeConfiguration($fontCategory);
        }
        $this->fontsCategories = $fontsCategoriesDto;
    }

    /**
     * @param array $colorsCategories
     *
     * @return void
     */
    private function setColorsCategories(array $colorsCategories): void
    {
        $colorsCategoriesDto = [];
        foreach ($colorsCategories as $colorCategory) {
            $colorsCategoriesDto[] = PsxDesignColorCategoryConfiguration::createFromThemeConfiguration($colorCategory);
        }
        $this->colorsCategories = $colorsCategoriesDto;
    }
}
