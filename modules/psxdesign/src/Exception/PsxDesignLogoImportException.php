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

namespace PrestaShop\Module\PsxDesign\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PsxDesignLogoImportException extends PsxDesignException
{
    /**
     * INFO SEVERITY 1-100
     */
    /** Then incorrect format is uploaded **/
    public const INVALID_FORMAT = 1;

    /** Then logo form destination is incorrect **/
    public const INVALID_DESTINATION = 2;

    /** Then header logo is svg format and user tries to apply it for mail, invoice **/
    public const LOGO_INCOMPATIBILITY = 3;

    /** Logo text field is empty **/
    public const EMPTY_TEXT = 5;

    /** Logo size is invalid **/
    public const INVALID_SIZE = 6;

    /** Logo font is invalid **/
    public const INVALID_FONT = 7;

    /** Logo font color is invalid **/
    public const INVALID_COLOR = 8;

    /** Logo font style is invalid **/
    public const INVALID_STYLE = 9;

    /** Logo font weight is invalid **/
    public const INVALID_WEIGHT = 10;

    /** Failed to replace old logo **/
    public const FAILED_REPLACE_LOGO = 11;

    /** Text length exceeded 64 characters */
    public const TEXT_LENGTH_EXCEEDED = 12;

    /**
     * ERROR SEVERITY 201-300
     */
    /** Insert logo into database table failed **/
    public const INSERT_LOGO_FAILED = 201;
}
