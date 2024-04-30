<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsEditionBasic\Helper;

class SetupGuideHelper
{
    public const DOMAINS_WHITE_LIST = [
        'localhost',
        '.mydada.shop',
        '.mypresta.shop',
    ];

    /**
     * Check if the domain name has been changed
     *
     * @return bool
     */
    public static function checklistDomain(): bool
    {
        return !array_filter(self::DOMAINS_WHITE_LIST, function ($domain) {
            return strpos($_SERVER['SERVER_NAME'], $domain) !== false;
        });
    }

    /**
     * Check if a product has been created
     *
     * @return bool
     */
    public static function checklistProduct(): bool
    {
        $products = \Product::getProducts(\Context::getContext()->language->id, 0, 0, 'id_product', 'ASC');
        $products = array_filter($products, function ($product) {
            return strpos($product['reference'], 'demo_') === false;
        });
        $product_count = count($products);

        return $product_count > 0;
    }

    /**
     * Check if account is linked
     *
     * @return bool
     */
    public static function checklistAccount(): bool
    {
        try {
            return (bool) \Configuration::get('PS_SETUP_GUIDE_STEP_ACCOUNT_AUTO_COMPLETED');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if the shop logo has been changed
     *
     * @return bool
     */
    public static function checklistLogo(): bool
    {
        return \Configuration::get('PS_LOGO') !== 'logo.png';
    }

    /**
     * Check if a step is completed
     *
     * @param string $stepName
     *
     * @return bool
     */
    public static function isStepCompleted(string $stepName): bool
    {
        return self::isStepUserCompleted($stepName) || self::isStepAutoCompleted($stepName);
    }

    /**
     * Check if a step is completed by the user
     *
     * @param string $stepName
     *
     * @return bool
     */
    public static function isStepUserCompleted(string $stepName): bool
    {
        return self::getStepUserCompletedStatus($stepName);
    }

    /**
     * Check if a step is auto-completed
     *
     * @param string $stepName
     *
     * @return bool
     */
    public static function isStepAutoCompleted(string $stepName): bool
    {
        $method = 'checklist' . ucfirst($stepName);

        if (!method_exists(self::class, $method)) {
            return false;
        }

        return self::$method();
    }

    /**
     * Get the user completed status of a step
     *
     * @param string $stepName
     *
     * @return bool
     */
    public static function getStepUserCompletedStatus(string $stepName): bool
    {
        $uc_stepName = \Tools::strtoupper($stepName);

        return (bool) \Configuration::get("PS_SETUP_GUIDE_STEP_{$uc_stepName}_USER_COMPLETED");
    }
}
