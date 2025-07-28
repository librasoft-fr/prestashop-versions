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

namespace PrestaShop\Module\PsxDesign\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignFontsException;
use PrestaShop\Module\PsxDesign\Processor\FontStylesheetsProcessor;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class FontStylesheetUpdater
{
    private $configurationProvider;
    private $modulePath;
    private $filesystem;
    private $processor;

    public function __construct(
        ThemeConfigurationProvider $configurationProvider,
        string $modulePath,
        Filesystem $filesystem,
        FontStylesheetsProcessor $processor
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->modulePath = $modulePath;
        $this->filesystem = $filesystem;
        $this->processor = $processor;
    }

    /**
     * @return void
     *
     * @throws PsxDesignFontsException
     */
    public function updateStylesheets(): void
    {
        if (!$this->configurationProvider->fonts->getFontFeatureAvailability()) {
            $this->removeStylesheets();

            return;
        }

        try {
            $this->processor->processStylesheetCreation();
        } catch (Throwable $e) {
            $this->removeStylesheets();
            throw new PsxDesignFontsException('Failed to create new stylesheets', PsxDesignFontsException::FAILED_TO_CREATE_STYLESHEET);
        }
    }

    /**
     * @return void
     */
    private function removeStylesheets(): void
    {
        foreach (PsxDesignConfig::getFontsStylesheetsPaths($this->modulePath) as $stylesheetPath) {
            if ($stylesheetPath && $this->filesystem->exists($stylesheetPath)) {
                $this->filesystem->remove($stylesheetPath);
            }
        }
    }
}
