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

namespace PrestaShop\Module\PsxDesign\Traits\Hooks;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;

trait UseActionFrontControllerSetMedia
{
    /**
     * @return void
     */
    public function hookActionFrontControllerSetMedia(): void
    {
        $modulePath = $this->getLocalPath();
        $moduleFolderName = 'modules/';

        $fontsCssSelectorStylesheet = PsxDesignConfig::getHashedStylesheetPathByFileName(PsxDesignConfig::FONTS_CSS_SELECTOR_STYLESHEET_FILE_NAME, $modulePath);
        if ($fontsCssSelectorStylesheet && file_exists($fontsCssSelectorStylesheet)) {
            $this->context->controller->registerStylesheet(
                'customFontsCss',
                strstr($fontsCssSelectorStylesheet, $moduleFolderName),
                [
                    'priority' => 70,
                ]
            );
        }

        $fontsCssVariableStylesheet = PsxDesignConfig::getHashedStylesheetPathByFileName(PsxDesignConfig::FONTS_CSS_VARIABLE_STYLESHEET_FILE_NAME, $modulePath);
        if ($fontsCssVariableStylesheet && file_exists($fontsCssVariableStylesheet)) {
            $this->context->controller->registerStylesheet(
                'customVariablesFontsCss',
                strstr($fontsCssVariableStylesheet, $moduleFolderName),
                [
                    'priority' => 70,
                ]
            );
        }

        $colorsCssSelectorStylesheet = PsxDesignConfig::getHashedStylesheetPathByFileName(PsxDesignConfig::COLORS_CSS_SELECTOR_STYLESHEET_FILE_NAME, $modulePath);
        if ($colorsCssSelectorStylesheet && file_exists($colorsCssSelectorStylesheet)) {
            $this->context->controller->registerStylesheet(
                'customCssSelectorColors',
                strstr($colorsCssSelectorStylesheet, $moduleFolderName),
                [
                    'priority' => 70,
                ]
            );
        }

        $colorsCssVariableStylesheet = PsxDesignConfig::getHashedStylesheetPathByFileName(PsxDesignConfig::COLORS_CSS_VARIABLE_STYLESHEET_FILE_NAME, $modulePath);
        if ($colorsCssVariableStylesheet && file_exists($colorsCssVariableStylesheet)) {
            $this->context->controller->registerStylesheet(
                'customCssVariablesColors',
                strstr($colorsCssVariableStylesheet, $moduleFolderName),
                [
                    'priority' => 70,
                ]
            );
        }

        $compiledThemeStylesheet = PsxDesignConfig::getHashedStylesheetPathByFileName(PsxDesignConfig::COMPILED_THEME_FILE_NAME, $modulePath);
        if ($compiledThemeStylesheet && file_exists($compiledThemeStylesheet)) {
            $this->context->controller->registerStylesheet(
                'theme-main',
                strstr($compiledThemeStylesheet, $moduleFolderName),
                [
                    'priority' => 49,
                ]
            );
        }
    }
}
