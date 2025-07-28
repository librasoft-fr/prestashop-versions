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

namespace PrestaShop\Module\PsxDesign\Validator;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorData;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignColorsException;

class ColorPaletteValidator
{
    /**
     * @param PsxDesignColorData[] $colors
     *
     * @return void
     */
    public function validateColors(array $colors): void
    {
        foreach ($colors as $color) {
            if (!$color->getValue()) {
                throw new PsxDesignColorsException('Color in color palette is invalid', PsxDesignColorsException::COLOR_IS_INVALID);
            }

            if (!$this->isCorrectHexColor($color->getValue())) {
                throw new PsxDesignColorsException('Wrong color format', PsxDesignColorsException::COLOR_IS_NOT_HEX_FORMAT);
            }
        }
    }

    /**
     * @param string $colorValue
     *
     * @return bool
     */
    protected function isCorrectHexColor(string $colorValue): bool
    {
        return (bool) preg_match('/^#[a-f0-9]{6}$/i', $colorValue);
    }
}
