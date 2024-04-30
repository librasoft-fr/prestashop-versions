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

class PsxDesignColorsException extends PsxDesignException
{
    /**
     * INFO SEVERITY 1-100
     */
    /** Color palette name is empty. **/
    public const EMPTY_COLOR_PALETTE_NAME = 2;

    /** Color is not valid. **/
    public const COLOR_IS_INVALID = 3;

    /** Color is not hex format. **/
    public const COLOR_IS_NOT_HEX_FORMAT = 11;

    /**
     * ERROR SEVERITY 201-300
     */

    /** SQL error, the color palette can not be added. */
    public const FAILED_TO_ADD_COLOR_PALETTE = 201;

    /** SQL error, the color palette can not be updated. */
    public const FAILED_TO_UPDATE_COLOR_PALETTE = 202;

    /** Error on stylesheet creation */
    public const FAILED_TO_CREATE_STYLESHEET = 203;

    /** SQL error, the color palette can not be added. */
    public const INVALID_TYPE = 204;

    /** Variable type does not exist */
    public const INVALID_VARIABLE_TYPE = 205;

    /** Failed to remove stylesheets */
    public const FAILED_TO_REMOVE_STYLESHEETS = 206;

    /** Failed to remove color */
    public const FAILED_TO_DELETE_COLOR = 207;
}
