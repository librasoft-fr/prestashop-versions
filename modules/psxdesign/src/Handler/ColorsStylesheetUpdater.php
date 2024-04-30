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
use PrestaShop\Module\PsxDesign\Exception\PsxDesignColorsException;
use PrestaShop\Module\PsxDesign\Processor\ColorsStylesheetsProcessor;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class ColorsStylesheetUpdater
{
    private $configurationProvider;
    private $modulePath;
    private $filesystem;
    private $processor;

    public function __construct(
        ThemeConfigurationProvider $configurationProvider,
        string $modulePath,
        Filesystem $filesystem,
        ColorsStylesheetsProcessor $processor
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->modulePath = $modulePath;
        $this->filesystem = $filesystem;
        $this->processor = $processor;
    }

    public function updateStylesheets(): void
    {
        $this->removeStylesheets();

        if (!$this->configurationProvider->colors->getColorFeatureAvailability() || empty($this->configurationProvider->colors->getCurrentDataThemeColorList())) {
            return;
        }

        try {
            $this->processor->processStylesheetCreation();
        } catch (Throwable $e) {
            $this->removeStylesheets();
            throw new PsxDesignColorsException('Failed to create new stylesheets', PsxDesignColorsException::FAILED_TO_CREATE_STYLESHEET);
        }
    }

    /**
     * @return void
     */
    private function removeStylesheets(): void
    {
        foreach (PsxDesignConfig::getColorsStylesheetsPaths($this->modulePath) as $stylesheetPath) {
            if ($stylesheetPath && $this->filesystem->exists($stylesheetPath)) {
                $this->filesystem->remove($stylesheetPath);
            }
        }
    }
}
