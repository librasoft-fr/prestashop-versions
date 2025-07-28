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

use MatthiasMullie\Minify;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignApiException;
use PrestaShop\Module\PsxDesign\Provider\FontDataProvider;
use PrestaShop\Module\PsxDesign\VO\Font\FontStyle;

class FontsStylesheetBuilder
{
    /**
     * @var FontDataProvider
     */
    private $fontsDataProvider;

    public function __construct(FontDataProvider $fontsDataProvider)
    {
        $this->fontsDataProvider = $fontsDataProvider;
    }

    /**
     * @param PsxDesignFontConfiguration[] $fonts
     *
     * @return string
     *
     * @throws PsxDesignApiException
     */
    public function buildCssSelectorFontsStylesheet(array $fonts): string
    {
        $stylesString = '';

        if (!$fonts) {
            return '';
        }

        foreach ($fonts as $font) {
            $fontStyle = new FontStyle($font->getStyle());
            $validFont = str_replace('+', ' ', $font->getFont());
            $sizeInRem = $this->pixelToRem($font->getSize());

            $stylesString .= "
                {$font->getVariableName()} {
                    font-family: '{$validFont}';
                    font-size: {$sizeInRem}rem;
                    font-style: {$fontStyle->getType()};
                    font-weight: {$fontStyle->getWeight()};
                }";
        }

        $url = $this->fontsDataProvider->buildFontUrl($fonts);
        $this->fontsDataProvider->validateUrl($url);
        $validUrl = "@import url('{$url}');";

        $minifier = new Minify\CSS();

        return $minifier->add($validUrl . $stylesString)->minify();
    }

    /**
     * @param PsxDesignFontConfiguration[] $fonts
     *
     * @return string
     *
     * @throws PsxDesignApiException
     */
    public function buildCssVariableFontsStylesheet(array $fonts): string
    {
        if (!$fonts) {
            return '';
        }

        $url = $this->fontsDataProvider->buildFontUrl($fonts);
        $this->fontsDataProvider->validateUrl($url);

        $stylesString = ':root{';

        foreach ($fonts as $font) {
            $fontStyle = new FontStyle($font->getStyle());
            $validFont = str_replace('+', ' ', $font->getFont());
            $sizeInRem = $this->pixelToRem($font->getSize());

            $stylesString .= "
                    {$font->getVariableName()}-font-family: '{$validFont}';
                    {$font->getVariableName()}-font-size: {$sizeInRem}rem;
                    {$font->getVariableName()}-font-style: {$fontStyle->getType()};
                    {$font->getVariableName()}-font-weight: {$fontStyle->getWeight()};
            ";
        }

        $validUrl = "@import url('{$url}');";

        $minifier = new Minify\CSS();

        $stylesString .= '}';

        return $minifier->add($validUrl . $stylesString)->minify();
    }

    /**
     * Function that converts a pixel value to a rem value
     *
     * @param int $pixelValue
     *
     * @return float
     */
    private function pixelToRem(int $pixelValue): float
    {
        $baseFontSize = 16;

        // Calculate the rem value by dividing the pixel value by the base font size
        $remValue = ($pixelValue / $baseFontSize);

        // Round the rem value to 3 decimal places
        return round($remValue, 3);
    }
}
