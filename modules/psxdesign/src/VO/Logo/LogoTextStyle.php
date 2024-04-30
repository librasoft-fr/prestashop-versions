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

namespace PrestaShop\Module\PsxDesign\VO\Logo;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Exception\PsxDesignLogoImportException;

class LogoTextStyle
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

    /**
     * @throws PsxDesignLogoImportException
     */
    public function __construct(string $style)
    {
        $this->assertLogoTextStyle($style);
        $this->style = $style;
    }

    /**
     * @throws PsxDesignLogoImportException
     */
    private function assertLogoTextStyle(string $style): void
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
     * @param string $type
     *
     * @return void
     *
     * @throws PsxDesignLogoImportException
     */
    private function assertType(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new PsxDesignLogoImportException(sprintf('Type %s is invalid. Allowed types are: %s', var_export($type, true), implode(', ', self::ALLOWED_TYPES)), PsxDesignLogoImportException::INVALID_DESTINATION);
        }
    }

    /**
     * @param int $weight
     *
     * @return void
     *
     * @throws PsxDesignLogoImportException
     */
    private function assertWeight(int $weight): void
    {
        if (!in_array($weight, self::ALLOWED_WEIGHTS, true)) {
            throw new PsxDesignLogoImportException(sprintf('Type %s is invalid. Allowed types are: %s', var_export($weight, true), implode(', ', self::ALLOWED_TYPES)), PsxDesignLogoImportException::INVALID_DESTINATION);
        }
    }

    /**
     * Format style properties to be compatible with api call to get correct font
     *
     * @return string
     */
    public function formatStyleForApiCall(): string
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
}
