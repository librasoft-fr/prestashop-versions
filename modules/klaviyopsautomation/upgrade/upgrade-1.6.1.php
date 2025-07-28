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

if (!defined('_PS_VERSION_')) {
    exit;
}

use KlaviyoPs\Classes\KlaviyoCouponUsageLimitType;

function upgrade_module_1_6_1($module)
{
    $module->registerHook('actionFrontControllerInitAfter');

    Configuration::updateValue('KLAVIYO_COUPON_USAGE_LIMIT_TYPE', KlaviyoCouponUsageLimitType::LIMIT_PREFIX);

    return true;
}
