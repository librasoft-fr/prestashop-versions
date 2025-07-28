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

namespace KlaviyoPs\Classes;

if (!defined('_PS_VERSION_')) {
    exit;
}

class KlaviyoCouponUsageLimitType
{
    const LIMIT_ONE = 'LIMIT_ONE';
    const LIMIT_PREFIX = 'LIMIT_PREFIX';
    const LIMIT_NONE = 'LIMIT_NONE';
}
