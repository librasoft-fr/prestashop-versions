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

use Configuration;
use CustomerCore;
use KlaviyoPsModule;
use KlaviyoPs\Classes\PrestashopServices\LoggerService;
use KlaviyoV3Sdk\Exception\KlaviyoApiException;
class HooksHandler
{
    /**
     * @var KlaviyoPsModule
     */
    const NEWSLETTER_SUBSCRIPTION = 0;
    const NEWSLETTER_UNSUBSCRIPTION = 1;
    const ERROR_SUBSCRIBING_CUSTOMER = 'Error while trying to subscribe to Klaviyo list, customer with email: ';
    private $klaviyoModule;

    /**
     * HooksHandler constructor.
     *
     * @param KlaviyoPsModule $klaviyopsModule
     */
    public function __construct(KlaviyoPsModule $klaviyopsModule)
    {
        $this->klaviyoModule = $klaviyopsModule;
    }

    /**
     * Handle actionCustomerAccount hooks. Includes add and update. Subscribe customer
     * to the Klaviyo list selected in module settings if they subscribed, are active
     * and aren't deleted.
     *
     * @param array $params
     */
    public function handleActionCustomerAccount(array $params)
    {
        $customer = $this->getCustomerFromHookParams($params);
        if (
            $customer->newsletter &&
            $customer->active &&
            !$customer->deleted &&
            Configuration::get('KLAVIYO_PRIVATE_API')
        ) {
            try {
                $api = new KlaviyoApiWrapper();
                $api->subscribeCustomer($customer->email);
            } catch (KlaviyoApiException $e) {
                $logger = new LoggerService();
                $logger->log('error', self::ERROR_SUBSCRIBING_CUSTOMER . $customer->email);
            }
        }
    }

    /**
     * Handle actionNewsletterSubscriptionAfter hook used in the default PrestaShop
     * Newsletter Subscription module.
     *
     * @param array $params
     */
    public function handleActionNewsletterSubscription(array $params)
    {
        if (
            $params['action'] == static::NEWSLETTER_SUBSCRIPTION &&
            !$params['error'] &&
            Configuration::get('KLAVIYO_PRIVATE_API')
        ) {
            try {
                $api = new KlaviyoApiWrapper();
                $api->subscribeCustomer($params['email']);
            } catch (KlaviyoApiException $e) {
                $logger = new LoggerService();
                $logger->log('error', self::ERROR_SUBSCRIBING_CUSTOMER . $params['email']);
            }
        }
    }

    /**
     * Return new Webservice Resource definition to use specific management interface.
     *
     * @param array $resources
     * @return array[]
     */
    public function handleAddWebserviceResources(array $resources)
    {
        return [
            'klaviyo' => [
                'description' => 'Klaviyo custom endpoints',
                'specific_management' => true,
            ]
        ];
    }

    /**
     * Extract Customer object from hook params.
     *
     * @param array $hookParams
     * @return CustomerCore
     */
    private function getCustomerFromHookParams(array $hookParams)
    {
        if (isset($hookParams['customer']) && $hookParams['customer'] instanceof CustomerCore) {
            return $hookParams['customer'];
        }

        if (isset($hookParams['newCustomer']) && $hookParams['newCustomer'] instanceof CustomerCore) {
            return $hookParams['newCustomer'];
        }
    }

    /**
     * Format custom properties for Klaviyo Subscribe request e.g. birthday, first and last name.
     *
     * @param CustomerCore $customer
     * @return array
     */
    private function getPropertiesFromCustomer(CustomerCore $customer)
    {
        $customerPropertiesMap = [
            'birthday' => 'Birthday',
            'firstname' => 'first_name',
            'lastname' => 'last_name',
        ];

        $customerProperties = [];
        foreach ($customerPropertiesMap as $customerProp => $klaviyoProp) {
            if (isset($customer->{$customerProp})) {
                $customerProperties[$klaviyoProp] = $customer->{$customerProp};
            }
        }

        return $customerProperties;
    }
}
