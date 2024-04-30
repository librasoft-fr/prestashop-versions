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

use InvalidArgumentException;
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignCompilerException;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use PrestaShop\Module\PsxDesign\Utility\DirectoryUtility;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\CompilationResult;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Compiler;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Exception\CompilerException;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\OutputStyle;
use RuntimeException;
use Shop;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Tools;

class ThemeStylesheetCompiler
{
    private const MANIFEST_FILE_NAME = 'manifest.json';

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

    /**
     * @var ThemeConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var string
     */
    private $themeName;

    public function __construct(
        string $modulePath,
        string $themesPath,
        Filesystem $fileSystem,
        ThemeConfigurationProvider $configurationProvider,
        int $shopId
    ) {
        $this->modulePath = $modulePath;
        $this->themesPath = $themesPath;
        $this->fileSystem = $fileSystem;
        $this->configurationProvider = $configurationProvider;
        $this->themeName = (new Shop($shopId))->theme_name;
    }

    /**
     * Process the replacement of the theme stylesheet.
     *
     * @return void
     *
     * @throws PsxDesignCompilerException
     * @throws CompilerException
     */
    public function processThemeStylesheetReplacement(): void
    {
        // Compile SCSS, update asset paths, replace CSS, and clean up
        $compiledScss = $this->compile();
        $compiledScss = $this->updateAssetsPath($compiledScss);
        $this->replaceStylesheet($compiledScss);
        $this->deleteTmpFolder();
    }

    /**
     * Compile theme SCSS files located in the module root directory,
     * generate CSS, and replace the current theme's CSS file with the new one.
     *
     * @return CompilationResult Compiled CSS
     *
     * @throws PsxDesignCompilerException
     * @throws CompilerException
     */

    // TODO : geré le cas du RTL (a voir)
    public function compile(): CompilationResult
    {
        try {
            $originalThemePath = $this->themesPath . $this->themeName;
            $tmpThemePath = $this->modulePath . PsxDesignConfig::TMP_DIR_NAME . DIRECTORY_SEPARATOR . $this->themeName;

            try {
                DirectoryUtility::copyDirectory($originalThemePath . DIRECTORY_SEPARATOR, $tmpThemePath);
            } catch (RuntimeException|InvalidArgumentException $e) {
                $this->deleteTmpFolder();
                throw new PsxDesignCompilerException($e->getMessage(), PsxDesignCompilerException::FAILED_TO_OVERWRITE_VARIABLES_SCSS);
            }

            $scssBaseFolder = $this->configurationProvider->global->getScssBaseFolder();

            if (is_dir($tmpThemePath . DIRECTORY_SEPARATOR . 'node_modules')) {
                DirectoryUtility::copyDirectory($tmpThemePath . '/node_modules', $tmpThemePath . $scssBaseFolder);
                $this->manageImports($tmpThemePath . $scssBaseFolder, $tmpThemePath . $scssBaseFolder);
            }

            $content = '';

            foreach ($this->configurationProvider->global->getScssFiles() as $file) {
                $content .= Tools::file_get_contents($tmpThemePath . $scssBaseFolder . DIRECTORY_SEPARATOR . $file);
            }
            $compiler = new Compiler();
            $compiler->setOutputStyle(OutputStyle::COMPRESSED);
            $compiler->setImportPaths($tmpThemePath . $scssBaseFolder . DIRECTORY_SEPARATOR);
            $result = $compiler->compileString($content);
        } catch (CompilerException $e) {
            $this->deleteTmpFolder();
            throw new PsxDesignCompilerException('Unable to compile scss files' . $e->getMessage(), PsxDesignCompilerException::FAILED_COMPILING);
        }

        return $result;
    }

    /**
     * Replace the current theme's compiled stylesheet with the newly generated CSS.
     *
     * @param string $result Compiled CSS content
     *
     * @return void
     *
     * @throws PsxDesignCompilerException
     */
    public function replaceStylesheet(string $result): void
    {
        try {
            $oldStylesheetPath = PsxDesignConfig::getHashedStylesheetPathByFileName(PsxDesignConfig::COMPILED_THEME_FILE_NAME, $this->modulePath);

            if ($oldStylesheetPath && $this->fileSystem->exists($oldStylesheetPath)) {
                $this->fileSystem->remove($oldStylesheetPath);
            }

            $newStylesheetPath = $this->modulePath . PsxDesignConfig::CUSTOM_STYLESHEETS_PATH . PsxDesignConfig::generateStylesheetHashedPathByFileName(PsxDesignConfig::COMPILED_THEME_FILE_NAME, $result);
            $this->fileSystem->dumpFile($newStylesheetPath, $result);
        } catch (IOException $e) {
            throw new PsxDesignCompilerException('Failed to overwrite theme compilated file.' . $e->getMessage(), PsxDesignCompilerException::FAILED_TO_OVERWRITE);
        }
    }

    /**
     * Recursively manage imports in SCSS files within the specified directory.
     *
     * @param string $currentDirectory
     * @param string $baseDirectory
     *
     * @return void
     */
    private function manageImports(string $currentDirectory, string $baseDirectory): void
    {
        $dir = opendir($currentDirectory);

        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($currentDirectory . DIRECTORY_SEPARATOR . $file)) {
                    $this->manageImports($currentDirectory . DIRECTORY_SEPARATOR . $file, $baseDirectory);
                } else {
                    $contents = Tools::file_get_contents($currentDirectory . DIRECTORY_SEPARATOR . $file);

                    $contents = $this->manageCssImport($contents, $currentDirectory, $baseDirectory);
                    $contents = $this->manageNodeModulesImport($contents);

                    file_put_contents($currentDirectory . '/' . $file, $contents);
                }
            }
        }
    }

    /**
     * Manage Node.js module imports in CSS content.
     *
     * @param string $cssContent
     *
     * @return string
     */
    private function manageNodeModulesImport(string $cssContent): string
    {
        return preg_replace('/(@import\s*["\']?)~/', '$1', $cssContent);
    }

    /**
     * Manage CSS imports by resolving relative paths and replacing them with the corresponding content.
     *
     * @param string $cssContent
     * @param string $currentDirectory
     * @param string $baseDirectory
     *
     * @return string
     */
    private function manageCssImport(string $cssContent, string $currentDirectory, string $baseDirectory): string
    {
        $cssImportPattern = '/@import\s+[\'"]([^\'"]+\.css)[\'"]/';

        return preg_replace_callback($cssImportPattern, function ($matches) use ($currentDirectory, $baseDirectory) {
            $importedFile = $matches[1];

            $fullPath = $importedFile;
            if (strpos($importedFile, '~') !== false) {
                $fullPath = str_replace('~', '', $fullPath);
                $fullPath = $baseDirectory . DIRECTORY_SEPARATOR . $fullPath;
            } else {
                $fullPath = $currentDirectory . DIRECTORY_SEPARATOR . $fullPath;
            }

            if (file_exists($fullPath)) {
                return Tools::file_get_contents($fullPath);
            }

            return $matches[0];
        }, $cssContent);
    }

    /**
     * Update asset paths in CSS content using a manifest file.
     *
     * @param CompilationResult $compiledScss
     *
     * @return string
     */
    private function updateAssetsPath(CompilationResult $compiledScss): string
    {
        $cssStylesheet = $compiledScss->getCss();

        $manifestPath = $this->modulePath . PsxDesignConfig::TMP_DIR_NAME . DIRECTORY_SEPARATOR . $this->themeName . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . self::MANIFEST_FILE_NAME;

        if (!file_exists($manifestPath)) {
            return $cssStylesheet;
        }
        $manifestJson = Tools::file_get_contents($manifestPath);

        $manifestData = json_decode($manifestJson, true);

        $simpleManifestData = [];
        foreach ($manifestData as $oldKey => $value) {
            $keyExploded = explode('/', $oldKey);
            $fileNameKey = end($keyExploded);
            $simpleManifestData[$fileNameKey] = $value;
        }

        $relativeUrlPattern = '/url\(\s*[\'"]?([^\'"\s]+)[\'"]?\s*\)/';

        return preg_replace_callback($relativeUrlPattern, function ($matches) use ($manifestData, $simpleManifestData) {
            $relativePath = $matches[1];

            $pathSegments = explode('/', $relativePath);

            while (!empty($pathSegments)) {
                $shortenedPath = implode('/', $pathSegments);

                if (isset($manifestData[$shortenedPath])) {
                    return 'url("' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $this->themeName . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . str_replace('../', '', $manifestData[$shortenedPath]) . '")';
                } elseif (isset($simpleManifestData[$shortenedPath])) {
                    return 'url("' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $this->themeName . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . str_replace('../', '', $simpleManifestData[$shortenedPath]) . '")';
                }

                array_shift($pathSegments);
            }

            return $matches[0];
        }, $cssStylesheet);
    }

    /**
     * Delete the temporary folder created during the compilation process.
     *
     * @return void
     *
     * @throws PsxDesignCompilerException
     */
    private function deleteTmpFolder(): void
    {
        try {
            DirectoryUtility::deleteDirectory($this->modulePath . PsxDesignConfig::TMP_DIR_NAME . DIRECTORY_SEPARATOR . $this->themeName);
        } catch (RuntimeException|InvalidArgumentException $e) {
            throw new PsxDesignCompilerException($e->getMessage(), PsxDesignCompilerException::FAILED_TO_OVERWRITE_VARIABLES_SCSS);
        }
    }
}
