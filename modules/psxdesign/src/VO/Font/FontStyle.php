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

namespace PrestaShop\Module\PsxDesign\VO\Font;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Exception\PsxDesignFontsException;

class FontStyle
{
    private const ALLOWED_TYPES = ['normal', 'italic'];
    private const ALLOWED_WEIGHTS = [100, 200, 300, 400, 500, 600, 700, 800, 900];

    private const ITALIC_TYPE = 'italic';

    /**
     * @var string
     */
    private $style;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $weight;

    public function __construct(string $style)
    {
        $this->assertFontStyle($style);
        $this->style = $style;
    }

    private function assertFontStyle(string $style): void
    {
        $explodedStyle = explode('-', $style);
        $type = $explodedStyle[0];
        $this->assertType($type);
        $this->type = $type;

        $weight = (int) $explodedStyle[1];
        $this->assertWeight($weight);
        $this->weight = $weight;
    }

    /**
     * Format style properties for correct style definition to google fonts
     *
     * @return string
     */
    public function formatStyleUrl(): string
    {
        if ($this->type === self::ITALIC_TYPE) {
            return 'ital,wght@1,' . $this->weight;
        }

        return 'wght@' . $this->weight;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->style;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    private function assertType(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new PsxDesignFontsException(sprintf('Type %s is invalid. Allowed types are: %s', $type, implode(', ', self::ALLOWED_TYPES)), PsxDesignFontsException::INVALID_TYPE);
        }
    }

    /**
     * @param int $weight
     *
     * @return void
     */
    private function assertWeight(int $weight): void
    {
        if (!in_array($weight, self::ALLOWED_WEIGHTS, true)) {
            throw new PsxDesignFontsException(sprintf('Font weight %s is invalid. Allowed weights are: %s', $weight, implode(', ', self::ALLOWED_TYPES)), PsxDesignFontsException::INVALID_WEIGHT);
        }
    }
}
