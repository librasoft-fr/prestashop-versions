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

use PrestaShop\Module\PsxDesign\Builder\FontsStylesheetBuilder;
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignApiException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignFontsException;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignFontsRepository;
use PrestaShop\Module\PsxDesign\Utility\ThemeConfiguration\FontUtility;
use Shop;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FontStylesheetsProcessor
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var ThemeConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var FontsStylesheetBuilder
     */
    private $fontsStylesheetBuilder;

    /**
     * @var FontUtility
     */
    private $fontUtility;

    /**
     * @var string
     */
    private $modulePath;

    /**
     * @var string
     */
    private $themeName;

    /**
     * @var PsxdesignFontsRepository
     */
    private $fontsRepository;

    /**
     * @var PsxDesignFontConfiguration[]|null
     */
    private $cssVariableFonts;

    /**
     * @var PsxDesignFontConfiguration[]|null
     */
    private $cssSelectorFonts;

    public function __construct(
        FileSystem $fileSystem,
        ThemeConfigurationProvider $configurationProvider,
        PsxdesignFontsRepository $fontsRepository,
        FontsStylesheetBuilder $fontsStylesheetBuilder,
        FontUtility $fontUtility,
        string $modulePath,
        int $shopId
    ) {
        $this->fileSystem = $fileSystem;
        $this->configurationProvider = $configurationProvider;
        $this->fontsRepository = $fontsRepository;
        $this->fontsStylesheetBuilder = $fontsStylesheetBuilder;
        $this->fontUtility = $fontUtility;
        $this->modulePath = $modulePath;
        $this->themeName = (new Shop($shopId))->theme_name;
    }

    /**
     * @return void
     *
     * @throws PsxDesignFontsException
     * @throws PsxDesignApiException
     */
    public function processStylesheetCreation(): void
    {
        $fontsData = $this->fontsRepository->getFontsByThemeName($this->themeName);
        $configurationFonts = $this->configurationProvider->fonts->getFonts();

        // We need to get used fonts so we can work with correct DTO
        // It's used to generate only the stylesheet changed by the merchant
        $fontsToGroup = $this->fontUtility->getUsedConfigurationsAccordingFontsData($configurationFonts, $fontsData);

        $groupedFonts = $this->fontUtility->groupFontsByType($fontsToGroup);
        $this->setFonts($groupedFonts);

        foreach (PsxDesignConfig::FONTS_TYPES as $type) {
            $content = '';
            $this->removeStylesheet(PsxDesignConfig::getFontsStylesheetFileNameByType($type));

            if ($type === PsxDesignConfig::CSS_VARIABLE && !empty($this->cssVariableFonts)) {
                $content = $this->fontsStylesheetBuilder->buildCssVariableFontsStylesheet($this->cssVariableFonts);
            }

            if ($type === PsxDesignConfig::CSS_SELECTOR && !empty($this->cssSelectorFonts)) {
                $content = $this->fontsStylesheetBuilder->buildCssSelectorFontsStylesheet($this->cssSelectorFonts);
            }

            if (empty($content)) {
                continue;
            }

            $this->replaceStylesheet($content, $type);
        }
    }

    /**
     * @param string $fileName
     *
     * @return void
     */
    private function removeStylesheet(string $fileName): void
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
     * @throws PsxDesignFontsException
     */
    private function replaceStylesheet(string $content, string $type): void
    {
        try {
            $styleSheetHashedName = PsxDesignConfig::generateStylesheetHashedPathByFileName(PsxDesignConfig::getFontsStylesheetFileNameByType($type), $content);
            $stylesheetPath = $this->modulePath . PsxDesignConfig::CUSTOM_STYLESHEETS_PATH . $styleSheetHashedName;

            $this->fileSystem->dumpFile($stylesheetPath, $content);
        } catch (IOException $e) {
            throw new PsxDesignFontsException('Failed to create stylesheet.' . $e->getMessage(), PsxDesignFontsException::FAILED_TO_CREATE_STYLESHEET);
        }
    }

    /**
     * @param array $groupedFonts
     *
     * @return void
     */
    private function setFonts(array $groupedFonts): void
    {
        $this->cssVariableFonts = $groupedFonts[PsxDesignConfig::CSS_VARIABLE] ?? [];
        $this->cssSelectorFonts = $groupedFonts[PsxDesignConfig::CSS_SELECTOR] ?? [];
    }
}
