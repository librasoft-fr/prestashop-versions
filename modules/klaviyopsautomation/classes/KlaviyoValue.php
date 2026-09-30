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

class KlaviyoValue
{
    const SERVICE = 'prestashop';
    const LIMIT_COUPON = 100000;

    const UTM_CAMPAIGN_EDITION = '701Nu000000ZIDNIA4';
    const UTM_CAMPAIGN_PROJECT = '701Nu000000ZCkhIAG';

    const WEBSERVICE_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const WEBSERVICE_CREATE_DELETE_LIMIT = 100;
    const WEBSERVICE_DEFAULT_BATCH_SIZE = 50;
    const WEBSERVICE_MAX_BATCH_SIZE = 100;
}
