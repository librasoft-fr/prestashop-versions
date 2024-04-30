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

use ArrayObject;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\PrestashopServices\CustomerService;

class CustomerEventService
{
    /**
     * @var CustomerService
     */
    protected $customerService;

    public function __construct(
        CustomerService $customerService
    ) {
        $this->customerService = $customerService;
    }

    /**
     * Build payload to identify a PrestaShop customer into Klaviyo
     *
     * @param ArrayObject $customer
     * @return array
     * @throws KlaviyoException
     */
    public function buildPayload(ArrayObject $customer)
    {
        if (!isset($customer['cache_payload'])) {
            $customer['cache_payload'] = [
                '$email' => $this->customerService->getEmail($customer),
            ];
        }

        return $customer['cache_payload'];
    }
}
