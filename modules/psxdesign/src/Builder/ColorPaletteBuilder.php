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

namespace PrestaShop\Module\PsxDesign\Builder;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorData;
use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorPalette;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use Symfony\Component\HttpFoundation\ParameterBag;

class ColorPaletteBuilder
{
    /**
     * @var ThemeConfigurationProvider
     */
    private $themeConfigurations;

    /**
     * @var string
     */
    private $themeName;

    public function __construct(ThemeConfigurationProvider $themeConfigurations, string $themeName)
    {
        $this->themeConfigurations = $themeConfigurations;
        $this->themeName = $themeName;
    }

    /**
     * @param bool $default
     * @param PsxDesignColorData[]|null $colors
     *
     * @return PsxDesignColorPalette
     */
    public function buildCurrentThemeColorsPalette(bool $default, array $colors = null): PsxDesignColorPalette
    {
        if (!$colors) {
            $colors = $this->themeConfigurations->colors->getColors();
        }

        $colorsArray = [];

        foreach ($colors as $color) {
            $colorsArray[] = $color->toArray();
        }

        $paletteName = $default ? 'default palette' : 'colors';
        $palette = new ParameterBag();
        $palette->set('name', $this->themeName . ' ' . $paletteName);
        $palette->set('default', (int) $default);
        $palette->set('theme', $this->themeName);
        $palette->set('colors', $colorsArray);

        return PsxDesignColorPalette::createFromBuilder($palette);
    }
}
