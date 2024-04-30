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

namespace PrestaShop\Module\PsxDesign\Factory\ThemeConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontCategoryConfiguration;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;

class FontsConfigurationFactory extends AbstractThemeConfigurationFactory
{
    public function __construct(string $themeName)
    {
        parent::__construct($themeName);
    }

    /**
     * @return PsxDesignFontConfiguration[]|null
     */
    public function getFonts(): ?array
    {
        return $this->configurations ? $this->configurations->getFonts() : null;
    }

    /**
     * @return PsxDesignFontCategoryConfiguration[]
     */
    public function getFontCategories(): array
    {
        if (!($this->configurations && $this->configurations->getFontsCategories())) {
            return [];
        }

        return $this->configurations->getFontsCategories();
    }

    /**
     * @return bool
     */
    public function provideFontFeatureAvailability(): bool
    {
        return $this->configurations && $this->configurations->getFonts();
    }
}
