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

namespace KlaviyoPs\Classes\KlaviyoServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use CartRule;
use KlaviyoPs\Classes\PrestashopServices\CartRuleService;
use Tools;

class CouponGeneratorService
{
    /**
     * @var CartRuleService
     */
    protected $cartRuleService;

    public function __construct(
        CartRuleService $cartRuleService
    ) {
        $this->cartRuleService = $cartRuleService;
    }

    /**
     * Duplicate the $cartRule by the number of $quantity entered
     * This function return each CartRule created into an array
     *
     * @param CartRule $cartRule
     * @param int $quantity
     * @return CartRule[]
     */
    public function generateFrom(
        CartRule $cartRule,
        $quantity = 1
    ) {
        $res = [];
        $codePrefix = $cartRule->code;

        for ($i = 0; $i < $quantity; $i++) {
            $codeSuffix = Tools::passwdGen(10, 'ALPHANUMERIC');
            $generatedCode = "{$codePrefix}_{$codeSuffix}";

            $res[] = $this->cartRuleService->duplicate($cartRule, [
                'code' => $generatedCode,
                'quantity' => 1,
                'quantity_per_user' => 1,
                'highlight' => 0,
                'active' => 1,
                'id_customer' => 0,
            ]);
        }

        return $res;
    }

    /**
     * Build payload to export CartRules into Klaviyo
     *
     * @param CartRule[] $cartRules
     * @return array
     */
    public function buildPayload($cartRules)
    {
        $res = [];

        foreach ($cartRules as $cr) {
            $res[] = [
                'Coupon Code' => $cr->code,
            ];
        }

        return $res;
    }
}
