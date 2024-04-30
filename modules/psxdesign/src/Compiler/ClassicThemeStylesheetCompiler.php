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

namespace PrestaShop\Module\PsxDesign\Compiler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignCompilerException;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\CompilationResult;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Compiler;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Exception\CompilerException;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\OutputStyle;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Tools;

class ClassicThemeStylesheetCompiler implements ThemeStylesheetCompilerInterface
{
    private const COMPILABLE_FILES_DIRECTORY = 'themes/classic';
    private const FILE_TO_OVERWRITE = '/assets/css/theme.css';
    private const MANIFEST_FILE_NAME = 'asset-manifest.json';
    private const PATH_TO_CUSTOM_CSS_FILE = 'themes/classic/partials';
    private const CUSTOM_VARIABLES_FILE = '_psxdesign_variables.scss';

    /**
     * @var string
     */
    private $modulePath;

    /**
     * @var string
     */
    private $themesPath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(string $modulePath, string $themesPath, Filesystem $fileSystem = null)
    {
        $this->modulePath = $modulePath;
        $this->themesPath = $themesPath;
        $this->fileSystem = $fileSystem ?? new Filesystem();
    }

    /**
     * @return void
     *
     * @throws PsxDesignCompilerException
     */
    public function processThemeStylesheetReplacement(string $content): void
    {
        $this->overwriteColorsToCompile($content);
        $compiledScss = $this->compile();
        $this->replaceCompiledThemeStylesheet($compiledScss);
    }

    /**
     * Compiles classic theme scss files located in module root dir
     * in css directory and replace current classic theme css file with new one.
     *
     * @return CompilationResult
     *
     * @throws PsxDesignCompilerException
     */
    public function compile(): CompilationResult
    {
        try {
            $content = Tools::file_get_contents($this->modulePath . self::COMPILABLE_FILES_DIRECTORY . '/theme.scss');
            $compiler = new Compiler();
            $compiler->setOutputStyle(OutputStyle::COMPRESSED);
            $compiler->setImportPaths($this->modulePath . self::COMPILABLE_FILES_DIRECTORY);
            $result = $compiler->compileString($content);
        } catch (CompilerException $e) {
            throw new PsxDesignCompilerException('Unable to compile scss files' . $e->getMessage(), PsxDesignCompilerException::FAILED_COMPILING);
        }

        return $result;
    }

    /**
     * Overwrites classic theme styles (css/theme.css)
     *
     * @param CompilationResult $result
     *
     * @return void
     *
     * @throws PsxDesignCompilerException
     */
    public function replaceCompiledThemeStylesheet(CompilationResult $result): void
    {
        try {
            $this->fileSystem->dumpFile($this->themesPath . PsxDesignConfig::CLASSIC_THEME_NAME . self::FILE_TO_OVERWRITE, $this->fixAssetsPathsForClassicTheme($result->getCss()));
        } catch (IOException $e) {
            throw new PsxDesignCompilerException('Failed to overwrite theme css file.' . $e->getMessage(), PsxDesignCompilerException::FAILED_TO_OVERWRITE);
        }
    }

    /**
     * @param string $content
     *
     * @return void
     *
     * @throws PsxDesignCompilerException
     */
    public function overwriteColorsToCompile(string $content): void
    {
        $fileToOverwrite = $this->modulePath . self::PATH_TO_CUSTOM_CSS_FILE . DIRECTORY_SEPARATOR . self::CUSTOM_VARIABLES_FILE;

        if (is_writable($fileToOverwrite) && $resource = fopen($fileToOverwrite, 'wb')) {
            fwrite($resource, $content);
            fclose($resource);

            return;
        }

        throw new PsxDesignCompilerException('File in ' . $fileToOverwrite . 'does not exist or not writable', PsxDesignCompilerException::FAILED_TO_OVERWRITE_VARIABLES_SCSS);
    }

    /**
     * Replaces assets import paths in classic theme compiled css
     *
     * @param string $cssWithFixedAssetsImportPaths
     *
     * @return string
     */
    private function fixAssetsPathsForClassicTheme(string $cssWithFixedAssetsImportPaths): string
    {
        $manifestFile = Tools::file_get_contents($this->modulePath . self::COMPILABLE_FILES_DIRECTORY . DIRECTORY_SEPARATOR . self::MANIFEST_FILE_NAME);
        $manifest = json_decode($manifestFile, true);

        foreach ($manifest as $key => $value) {
            $cssWithFixedAssetsImportPaths = str_replace($key, $value, $cssWithFixedAssetsImportPaths);
        }

        return $cssWithFixedAssetsImportPaths;
    }
}
