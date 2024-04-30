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

class PsxDesignConfig
{
    // General offten needed values
    public const CLASSIC_THEME_NAME = 'classic';

    // Generated stylesheet files path and file names
    public const TMP_DIR_NAME = 'tmp';
    public const CUSTOM_STYLESHEETS_PATH = 'views/assets/customs/';
    public const COLORS_CSS_SELECTOR_STYLESHEET_FILE_NAME = 'psxdesign-colors-css-selectors.css';
    public const COLORS_CSS_VARIABLE_STYLESHEET_FILE_NAME = 'psxdesign-colors-css-variables.css';
    public const COLORS_SCSS_VARIABLE_STYLESHEET_FILE_NAME = '_psxdesign-variables.scss';
    public const FONTS_CSS_SELECTOR_STYLESHEET_FILE_NAME = 'psxdesign-fonts-css-selectors.css';
    public const FONTS_CSS_VARIABLE_STYLESHEET_FILE_NAME = 'psxdesign-fonts-css-variables.css';
    public const COMPILED_THEME_FILE_NAME = 'psxdesign-compiled-theme.css';
    public const SCSS_VARIABLE = 'scss_variable';
    public const CSS_VARIABLE = 'css_variable';
    public const CSS_SELECTOR = 'css_selector';
    public const COLORS_TYPES = [
        self::CSS_VARIABLE,
        self::CSS_SELECTOR,
        self::SCSS_VARIABLE,
    ];
    public const FONTS_TYPES = [
        self::CSS_VARIABLE,
        self::CSS_SELECTOR,
    ];

    /**
     * @param string $fileName
     * @param string $content
     *
     * @return string|null
     */
    public static function generateStylesheetHashedPathByFileName(string $fileName, string $content): ?string
    {
        $baseFileNameInfo = pathinfo($fileName);

        $hash = substr(hash('sha256', $content), 0, 10);

        return $baseFileNameInfo['filename'] . '-' . $hash . '.' . $baseFileNameInfo['extension'];
    }

    /**
     * @param string $fileName
     * @param string $modulePath
     *
     * @return string|null
     */
    public static function getHashedStylesheetPathByFileName(string $fileName, string $modulePath): ?string
    {
        $baseFileNameInfo = pathinfo($fileName);

        $matchingFiles = glob($modulePath . self::CUSTOM_STYLESHEETS_PATH . $baseFileNameInfo['filename'] . '*.' . $baseFileNameInfo['extension']);

        if (!empty($matchingFiles)) {
            return $matchingFiles[0];
        }

        return null;
    }

    /**
     * @param string $modulePath
     *
     * @return string[]
     */
    public static function getColorsStylesheetsPaths(string $modulePath): array
    {
        return [
            self::getHashedStylesheetPathByFileName(self::COLORS_CSS_SELECTOR_STYLESHEET_FILE_NAME, $modulePath),
            self::getHashedStylesheetPathByFileName(self::COLORS_CSS_VARIABLE_STYLESHEET_FILE_NAME, $modulePath),
            self::getHashedStylesheetPathByFileName(self::COMPILED_THEME_FILE_NAME, $modulePath),
        ];
    }

    /**
     * @param string $modulePath
     *
     * @return string[]
     */
    public static function getFontsStylesheetsPaths(string $modulePath): array
    {
        return [
            self::getHashedStylesheetPathByFileName(self::FONTS_CSS_SELECTOR_STYLESHEET_FILE_NAME, $modulePath),
            self::getHashedStylesheetPathByFileName(self::FONTS_CSS_VARIABLE_STYLESHEET_FILE_NAME, $modulePath),
        ];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function getColorsStylesheetFileNameByType(string $type): ?string
    {
        switch ($type) {
            case self::CSS_VARIABLE:
                return self::COLORS_CSS_VARIABLE_STYLESHEET_FILE_NAME;
            case self::CSS_SELECTOR:
                return self::COLORS_CSS_SELECTOR_STYLESHEET_FILE_NAME;
            case self::SCSS_VARIABLE:
                return self::COLORS_SCSS_VARIABLE_STYLESHEET_FILE_NAME;
            default:
                return null;
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function getFontsStylesheetFileNameByType(string $type): ?string
    {
        switch ($type) {
            case self::CSS_VARIABLE:
                return self::FONTS_CSS_VARIABLE_STYLESHEET_FILE_NAME;
            case self::CSS_SELECTOR:
                return self::FONTS_CSS_SELECTOR_STYLESHEET_FILE_NAME;
            default:
                return null;
        }
    }
}
