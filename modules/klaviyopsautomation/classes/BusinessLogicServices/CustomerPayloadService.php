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

namespace KlaviyoPs\Classes\BusinessLogicServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Customer;
use ObjectModelCore;
use KlaviyoPs\Classes\BusinessLogicServices\PayloadServiceInterface;

class CustomerPayloadService extends PayloadServiceInterface
{
    const SENSITIVE_KEYS = array(
        'last_passwd_gen', 'passwd', 'reset_password_token',
        'reset_password_validity', 'secure_key',
    );

    /**
     * @inheritDoc
     */
    public static function buildPayload(ObjectModelCore $customer, $id_shop = null)
    {
        return array_merge(
            self::removeSensitiveKeys($customer, self::SENSITIVE_KEYS),
            array('addresses' => array_values($customer->getSimpleAddresses()))
        );
    }
}
