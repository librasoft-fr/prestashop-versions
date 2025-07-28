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

use ArrayObject;
use Customer;
use KlaviyoPs\Classes\KlaviyoServices\CustomerEventService;
use KlaviyoPs\Classes\PrestashopServices\ContextService;
use KlaviyoPs\Classes\PrestashopServices\CustomerService;
use KlaviyoPsModule;
use ObjectModelCore;
use KlaviyoPs\Classes\BusinessLogicServices\PayloadServiceInterface;

class CustomerPayloadService extends PayloadServiceInterface
{
    const SENSITIVE_KEYS = array(
        'last_passwd_gen', 'passwd', 'reset_password_token',
        'reset_password_validity', 'secure_key',
    );

    /**
     * @param Customer $customer
     * @param ArrayObject|null $normalizedCustomer use this parameter to force using this normalized Customer / Context
     * @inheritDoc
     */
    public static function buildPayload(
        ObjectModelCore $customer,
        $id_shop = null,
        $normalizedCustomer = null
    ) {
        $module = KlaviyoPsModule::getInstance();
        /** @var ContextService $contextService */
        $contextService = $module->getService('klaviyops.prestashop_services.context');
        /** @var CustomerService $customerService */
        $customerService = $module->getService('klaviyops.prestashop_services.customer');
        /** @var CustomerEventService $customerEventService */
        $customerEventService = $module->getService('klaviyops.klaviyo_service.customer_event_service');

        if ($normalizedCustomer === null) {
            $normalizedContext = $contextService->normalize([
                'id_shop' => $id_shop,
            ]);
            $normalizedCustomer = $customerService->normalize(
                $customer,
                $normalizedContext
            );
        }

        return array_merge(
            self::removeSensitiveKeys($customer, self::SENSITIVE_KEYS),
            array('addresses' => array_values($customer->getSimpleAddresses())),
            $customerEventService->buildPayloadForWebservice($normalizedCustomer)
        );
    }
}
