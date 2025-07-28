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

use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignThemeException;
use PrestaShop\PrestaShop\Core\Addon\AddonInterface;
use PrestaShop\PrestaShop\Core\Addon\Theme\Theme;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeRepository;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Theme\Command\ImportThemeCommand;
use PrestaShop\PrestaShop\Core\Domain\Theme\ValueObject\ThemeImportSource;

class ThemeUploader
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var ThemeRepository
     */
    private $themeRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        ThemeRepository $themeRepository
    ) {
        $this->commandBus = $commandBus;
        $this->themeRepository = $themeRepository;
    }

    /**
     * @param ThemeImportSource $themeImportSource
     *
     * @return Theme
     *
     * @throws PsxDesignException
     */
    public function upload(ThemeImportSource $themeImportSource): Theme
    {
        $previousThemes = $this->themeRepository->getList();

        $this->commandBus->handle(new ImportThemeCommand($themeImportSource));

        return $this->getImportedTheme($previousThemes);
    }

    /**
     * @param array<AddonInterface> $previousThemes
     *
     * @return Theme
     */
    private function getImportedTheme(array $previousThemes): Theme
    {
        $existedThemesNames = [];

        /** @var Theme $theme */
        foreach ($previousThemes as $theme) {
            $existedThemesNames[] = $theme->getName();
        }

        $importedThemes = $this->themeRepository->getListExcluding($existedThemesNames);

        if (empty($importedThemes)) {
            throw new PsxDesignThemeException('Failed to find imported theme', PsxDesignThemeException::FAILED_FIND_IMPORTED_THEME);
        }

        return array_shift($importedThemes);
    }
}
