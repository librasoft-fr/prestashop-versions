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

namespace PrestaShop\Module\PsxDesign\Processor;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Builder\ColorsStylesheetBuilder;
use PrestaShop\Module\PsxDesign\Compiler\ClassicThemeStylesheetCompiler;
use PrestaShop\Module\PsxDesign\Compiler\ThemeStylesheetCompiler;
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color\PsxDesignColorConfiguration;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignColorsException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignCompilerException;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use PrestaShop\Module\PsxDesign\Utility\ThemeConfiguration\ColorUtility;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Exception\CompilerException;
use Shop;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class ColorsStylesheetsProcessor
{
    /**
     * @var ClassicThemeStylesheetCompiler
     */
    private $classicThemeCompiler;

    /**
     * @var ThemeStylesheetCompiler
     */
    private $themeCompiler;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var ThemeConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var ColorsStylesheetBuilder
     */
    private $stylesheetBuilder;

    /**
     * @var ColorUtility
     */
    private $colorUtility;

    /**
     * @var string
     */
    private $modulePath;

    /**
     * @var string
     */
    private $themesPath;

    /**
     * @var string
     */
    private $themeName;

    /**
     * @var PsxDesignColorConfiguration[]|null
     */
    private $scssVariableColors;

    /**
     * @var PsxDesignColorConfiguration[]|null
     */
    private $cssVariableColors;

    /**
     * @var PsxDesignColorConfiguration[]|null
     */
    private $cssSelectorColors;

    public function __construct(
        ClassicThemeStylesheetCompiler $classicThemeCompiler,
        ThemeStylesheetCompiler $themeCompiler,
        Filesystem $fileSystem,
        ThemeConfigurationProvider $configurationProvider,
        ColorsStylesheetBuilder $stylesheetBuilder,
        ColorUtility $colorUtility,
        string $modulePath,
        string $themesPath,
        int $shopId
    ) {
        $this->classicThemeCompiler = $classicThemeCompiler;
        $this->themeCompiler = $themeCompiler;
        $this->fileSystem = $fileSystem;
        $this->configurationProvider = $configurationProvider;
        $this->stylesheetBuilder = $stylesheetBuilder;
        $this->colorUtility = $colorUtility;
        $this->modulePath = $modulePath;
        $this->themesPath = $themesPath;
        $this->themeName = (new Shop($shopId))->theme_name;
    }

    /**
     * @return void
     *
     * @throws PsxDesignColorsException
     * @throws PsxDesignCompilerException
     */
    public function processStylesheetCreation(): void
    {
        $colors = $this->configurationProvider->colors->getCurrentThemeColorList();
        $groupedColors = $this->colorUtility->groupColorsByType($colors);
        $this->setColors($groupedColors);

        // Generate stylesheets for all color types
        foreach (PsxDesignConfig::COLORS_TYPES as $type) {
            $content = '';
            $this->removeStylesheet(PsxDesignConfig::getColorsStylesheetFileNameByType($type));

            if ($type === PsxDesignConfig::CSS_VARIABLE && !empty($this->cssVariableColors)) {
                $content = $this->stylesheetBuilder->buildCssVariableStylesheetString($this->cssVariableColors);
            }

            if ($type === PsxDesignConfig::CSS_SELECTOR && !empty($this->cssSelectorColors)) {
                $content = $this->stylesheetBuilder->buildCssSelectorStylesheetString($this->cssSelectorColors);
            }

            if ($type === PsxDesignConfig::SCSS_VARIABLE && !empty($this->scssVariableColors)) {
                $content = $this->stylesheetBuilder->buildScssVariableStylesheetString($this->scssVariableColors);
            }

            if (empty($content)) {
                continue;
            }

            $this->replaceStylesheet($content, $type);

            if ($this->themeName === PsxDesignConfig::CLASSIC_THEME_NAME) {
                $this->classicThemeCompiler->processThemeStylesheetReplacement($content);
            } elseif ($type === PsxDesignConfig::SCSS_VARIABLE && !empty($this->scssVariableColors) && $this->configurationProvider->global->scssFeatureAvailability()) {
                try {
                    $this->themeCompiler->processThemeStylesheetReplacement();
                } catch (PsxDesignCompilerException|CompilerException $e) {
                    throw new PsxDesignCompilerException('Unable to compile scss files' . $e->getMessage(), PsxDesignCompilerException::FAILED_TO_OVERWRITE_VARIABLES_SCSS);
                }
            }
        }
    }

    /**
     * @param string $fileName
     *
     * @return void
     */
    protected function removeStylesheet(string $fileName): void
    {
        $stylesheetPath = PsxDesignConfig::getHashedStylesheetPathByFileName($fileName, $this->modulePath);

        if ($stylesheetPath && $this->fileSystem->exists($stylesheetPath)) {
            $this->fileSystem->remove($stylesheetPath);
        }
    }

    /**
     * @param string $content
     * @param string $type
     *
     * @return void
     *
     * @throws PsxDesignColorsException
     */
    protected function replaceStylesheet(string $content, string $type): void
    {
        try {
            if ($type === PsxDesignConfig::SCSS_VARIABLE) {
                $stylesheetPath = $this->themesPath . $this->themeName . $this->configurationProvider->global->getScssBaseFolder() . DIRECTORY_SEPARATOR . PsxDesignConfig::getColorsStylesheetFileNameByType($type);
            } else {
                $styleSheetHashedName = PsxDesignConfig::generateStylesheetHashedPathByFileName(PsxDesignConfig::getColorsStylesheetFileNameByType($type), $content);
                $stylesheetPath = $this->modulePath . PsxDesignConfig::CUSTOM_STYLESHEETS_PATH . $styleSheetHashedName;
            }
            $this->fileSystem->dumpFile($stylesheetPath, $content);
        } catch (IOException $e) {
            throw new PsxDesignColorsException('Failed to create stylesheet.' . $e->getMessage(), PsxDesignColorsException::FAILED_TO_CREATE_STYLESHEET);
        }
    }

    /**
     * @param array $groupedColors
     *
     * @return void
     */
    private function setColors(array $groupedColors): void
    {
        $this->cssVariableColors = $groupedColors[PsxDesignConfig::CSS_VARIABLE] ?? [];
        $this->scssVariableColors = $groupedColors[PsxDesignConfig::SCSS_VARIABLE] ?? [];
        $this->cssSelectorColors = $groupedColors[PsxDesignConfig::CSS_SELECTOR] ?? [];
    }
}
