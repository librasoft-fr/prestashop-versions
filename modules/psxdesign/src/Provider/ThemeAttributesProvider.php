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

namespace PrestaShop\Module\PsxDesign\Provider;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Link;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeProviderInterface;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeRepository;

class ThemeAttributesProvider
{
    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var ThemeImagesProvider
     */
    private $imagesProvider;

    /**
     * @var ThemeRepository
     */
    private $themeRepository;

    /**
     * @var Link
     */
    private $link;

    /**
     * @var ThemeConfigurationProvider
     */
    private $themeConfigurationProvider;

    public function __construct(
        ThemeProviderInterface $themeProvider,
        ThemeImagesProvider $imagesProvider,
        ThemeRepository $themeRepository,
        Link $link,
        ThemeConfigurationProvider $themeConfigurationProvider
    ) {
        $this->themeProvider = $themeProvider;
        $this->imagesProvider = $imagesProvider;
        $this->themeRepository = $themeRepository;
        $this->link = $link;
        $this->themeConfigurationProvider = $themeConfigurationProvider;
    }

    /**
     * Provides current theme with additional images
     *
     * @return array<string, mixed>
     */
    public function getCurrentThemeAttributes(): array
    {
        $currentTheme = $this->themeProvider->getCurrentlyUsedTheme();
        $images = $this->imagesProvider->getCurrentThemeImages($currentTheme->getName());

        $currentThemeArray = $currentTheme->get();
        $currentThemeArray['previewMobile'] = $images->getMobileImageUrl();
        $currentThemeArray['previewTablet'] = $images->getTabletImageUrl();
        $currentThemeArray['isActive'] = true;
        $currentThemeArray['customizeUrl'] = $this->getThemeCustomizeButtonUrl($currentTheme->getName());

        return $currentThemeArray;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getNotUsedThemesAttributes(): array
    {
        $notUsedThemesArray = [];
        foreach ($this->themeProvider->getNotUsedThemes() as $themeName => $theme) {
            $notUsedThemesArray[$themeName] = $theme->get();

            //todo delete if and uncomment next line to get url from config file
            $notUsedThemesArray[$themeName]['demoUrl'] = $this->getNotUsedThemesDemoUrl($themeName);

            $notUsedThemesArray[$themeName]['isActive'] = false;
        }

        return $notUsedThemesArray;
    }

    /**
     * @throws \PrestaShopException
     */
    private function getNotUsedThemesDemoUrl(string $themeName): ?string
    {
        return $this->themeRepository->getInstanceByName($themeName)->get('demo');
    }

    /**
     * Provides custom url to the module by the theme
     *
     * @param string $currentThemeName
     *
     * @return string
     */
    private function getThemeCustomizeButtonUrl(string $currentThemeName): string
    {
        if ($this->themeConfigurationProvider->global->getCustomizeUrl()) {
            return $this->themeConfigurationProvider->global->getCustomizeUrl();
        }

        return $this->link->getAdminLink('AdminModule', true, ['route' => 'admin_module_manage']);
    }
}
