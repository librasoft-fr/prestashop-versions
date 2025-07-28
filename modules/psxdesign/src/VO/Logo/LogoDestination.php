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
use PrestaShop\PrestaShop\Core\Domain\Shop\DTO\ShopLogoSettings;

class LogoDestination
{
    public const HEADER = 'header';
    public const EMAIL = 'email';
    public const INVOICE = 'invoice';

    private const ALLOWED_TYPES = [self::HEADER, self::EMAIL, self::INVOICE];

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $destinationField;

    /**
     * @param string $type
     *
     * @throws PsxDesignLogoImportException
     */
    public function __construct(string $type)
    {
        $this->assertIsValidType($type);
        $this->type = $type;
        $this->destinationField = $this->setDestinationByType();
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->type;
    }

    public function getDestinationFieldName(): string
    {
        return $this->destinationField;
    }

    /**
     * @param string $type
     *
     * @throws PsxDesignLogoImportException
     */
    private function assertIsValidType(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new PsxDesignLogoImportException(sprintf('Type %s is invalid. Supported types are: %s', var_export($type, true), implode(', ', self::ALLOWED_TYPES)), PsxDesignLogoImportException::INVALID_DESTINATION);
        }
    }

    /**
     * @return string
     */
    private function setDestinationByType(): string
    {
        $map = [
            self::HEADER => ShopLogoSettings::HEADER_LOGO_FILE_NAME,
            self::EMAIL => ShopLogoSettings::MAIL_LOGO_FILE_NAME,
            self::INVOICE => ShopLogoSettings::INVOICE_LOGO_FILE_NAME,
        ];

        return $map[$this->type];
    }
}
