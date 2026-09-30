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
use KlaviyoPs\Classes\KlaviyoApiWrapper;
use KlaviyoV3Sdk\Exception\KlaviyoApiException;

class ProfileEventService
{
    /**
     * @var KlaviyoApiWrapper
     */
    protected $klaviyoApiWrapper;

    /**
     * @var CustomerEventService
     */
    protected $customerEventService;

    public function __construct(
        KlaviyoApiWrapper $klaviyoApiWrapper,
        CustomerEventService $customerEventService
    ) {
        $this->klaviyoApiWrapper = $klaviyoApiWrapper;
        $this->customerEventService = $customerEventService;
    }

    /**
     * @param string $event
     * @return void
     * @throws KlaviyoApiException
     */
    public function track($event, ArrayObject $customer)
    {
        $this->klaviyoApiWrapper->trackEvent([
            'event' => $event,
            'properties' => [],
            'customer_properties' => $this->customerEventService->buildPayload(
                $customer
            ),
        ]);
    }
}
