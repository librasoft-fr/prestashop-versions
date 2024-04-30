<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

namespace KlaviyoPs\Classes\PrestashopServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use CartRule;
use PrestaShopDatabaseException;
use PrestaShopException;

class CartRuleService
{
    /**
     * Duplicate a CartRule and change properties with $params
     *
     * @param CartRule $cartRuleSource
     * @param array $params
     * @return CartRule
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function duplicate(
        CartRule $cartRuleSource,
        $params
    ) {
        $cartRuleDuplicated = new CartRule((int)$cartRuleSource->id);

        foreach ($params as $key => $value) {
            $cartRuleDuplicated->{$key} = $value;
        }

        $cartRuleDuplicated->add();
        CartRule::copyConditions(
            (int)$cartRuleSource->id,
            (int)$cartRuleDuplicated->id
        );

        return $cartRuleDuplicated;
    }
}
