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

use Address;
use Country;
use Configuration;
use Context;
use Customer;
use CustomerCore;
use Exception;
use FormField;
use Tools;
use Validate;
use KlaviyoPsModule;
use KlaviyoPs\Classes\KlaviyoServices\ProfileEventService;
use KlaviyoPs\Classes\PrestashopServices\ContextService;
use KlaviyoPs\Classes\PrestashopServices\CustomerService;
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
    const ERROR_SUBSCRIBING_SMS_CUSTOMER = 'Error while trying to subscribe to Klaviyo list, customer with sms: ';
    const ERROR_UPDATING_CUSTOM_PROPERTIES = 'Error while trying to update custom properties for customer with email: ';

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
     * @param string $event
     * @return void
     */
    public function handleActionCustomerAccount(array $params, $event)
    {
        try {
            /** @var ContextService $contextService */
            $contextService = $this->klaviyoModule->getService('klaviyops.prestashop_services.context');
            /** @var CustomerService $customerService */
            $customerService = $this->klaviyoModule->getService('klaviyops.prestashop_services.customer');
            /** @var ProfileEventService $profileEventService */
            $profileEventService = $this->klaviyoModule->getService('klaviyops.klaviyo_service.profile_event');

            $api = new KlaviyoApiWrapper();
            $customer = $this->getCustomerFromHookParams($params);

            if ($customer === null) {
                return;
            }

            $normalizedContext = $contextService->normalize(); // Current context
            $normalizedCustomer = $customerService->normalize(
                $customer,
                $normalizedContext
            );

            if (
                $customer->newsletter &&
                $customer->active &&
                !$customer->deleted &&
                Configuration::get('KLAVIYO_PRIVATE_API')
            ) {
                try {
                    $api->subscribeCustomer($customer->email);
                } catch (KlaviyoApiException $e) {
                    /** @var LoggerService $logger */
                    $logger = $this->klaviyoModule->getService('klaviyops.prestashop_services.logger');

                    $logger->log('error', self::ERROR_SUBSCRIBING_CUSTOMER . $customer->email);
                }
            }

            try {
                $profileEventService->track($event, $normalizedCustomer);
            } catch (KlaviyoApiException $e) {
                /** @var LoggerService $logger */
                $logger = $this->klaviyoModule->getService('klaviyops.prestashop_services.logger');

                $logger->log('error', self::ERROR_UPDATING_CUSTOM_PROPERTIES . $customer->email);
            }
        } catch (Exception $e) {
            /** @var LoggerService $logger */
            $logger = $this->klaviyoModule->getService('klaviyops.prestashop_services.logger');

            if (Validate::isLoadedObject($customer)) {
                $logger->log('error', "An error occured in handleActionCustomerAccount for customer {$customer->email}");
            } else {
                $logger->log('error', 'An error occured in handleActionCustomerAccount');
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
     * Add SMS Consent to customer address form
     *
     * @param  array $params
     * @return array
     */
    public function handleAdditionalCustomerAddressFields(array $params)
    {
        try {
            if (!$this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS')) {
                throw new KlaviyoApiException('Sync SMS Subscribers disabled');
            }

            $trigger = $this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_SMS_SUBSCRIBE_TRIGGER');
            if (!$trigger) {
                throw new KlaviyoApiException('SMS Subscribe trigger not configured');
            }

            $idLang = (int) Context::getContext()->language->id;

            $smsConsentLabel = $this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_SMS_CONSENT_LABEL', $idLang);
            $smsConsentDisclosure = $this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT', $idLang);
            $label = "{$smsConsentLabel}<br><em>{$smsConsentDisclosure}</em>";

            $formField = new FormField();
            $formField->setName('kl_sms_consent');
            $formField->setType('checkbox');
            $formField->setLabel($label);
            $formField->setRequired(false);

            return [$formField];
        } catch (KlaviyoApiException $e) {
            return [];
        }
    }

    /**
     * Subscribe mobile to klaviyo list
     *
     * @param  array $params
     * @return void
     */
    public function handleActionSubmitCustomerAddressForm(array $params)
    {
        if (
            !empty($params['address']) &&
            is_object($params['address']) &&
            Configuration::get('KLAVIYO_PRIVATE_API') &&
            isset($params['address']->kl_sms_consent) &&
            !empty($params['address']->kl_sms_consent)
        ) {
            $context = Context::getContext();

            if (!$this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS')) {
                return;
            }

            $trigger = $this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_SMS_SUBSCRIBE_TRIGGER');
            if ($trigger === 'place_order') {
                // Save information in cookie to be triggered after payment (place order)
                $cookie = $context->cookie;
                $addressFields = [
                    $params['address']->phone ?? '',
                    $params['address']->phone_mobile ?? '',
                ];
                $addressToHash = implode('', array_map('strval', $addressFields));
                $cookie->kl_sms_consent = Tools::hash($addressToHash);
                return;
            }

            // Trigger start checkout
            $mobile = $params['address']->phone_mobile ?? $params['address']->phone ?? '';
            $isoCode = '';
            if (!empty($params['address']->id_country)) {
                $country = new Country($params['address']->id_country);
                if (Validate::isLoadedObject($country)) {
                    $isoCode = $country->iso_code;
                }
            }

            $attributes = ['mobile' => $mobile];

            $customer = new Customer((int)$params['address']->id_customer);

            if (!Validate::isLoadedObject($customer)) {
                $customer = $context->customer;
            }

            if (Validate::isLoadedObject($customer)) {
                $attributes['email'] = $customer->email;
            }

            $this->sendSMSConsentRequestAPI($attributes, $isoCode);
        }
    }

    /**
     * Subscribe mobile to klaviyo list (place order trigger)
     *
     * @param  array $params
     * @return void
     */
    public function handleDisplayOrderConfirmation($params)
    {
        $cookie = Context::getContext()->cookie;

        if (
            !empty($params['order']) &&
            Validate::isLoadedObject($params['order']) &&
            Configuration::get('KLAVIYO_PRIVATE_API') &&
            isset($cookie->kl_sms_consent) &&
            !empty($cookie->kl_sms_consent)
        ) {
            if (!$this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS')) {
                return;
            }

            $trigger = $this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_SMS_SUBSCRIBE_TRIGGER');
            if ($trigger !== 'place_order') {
                return;
            }

            // Check consent to invoice address
            $consentAddress = null;
            foreach (
                [
                    $params['order']->id_address_invoice,
                    $params['order']->id_address_delivery
                ] as $idAddress
            ) {
                $address = new Address((int)$idAddress);
                if (Validate::isLoadedObject($address)) {
                    $addressFields = [
                        $address->phone ?? '',
                        $address->phone_mobile ?? '',
                    ];
                    $addressToHash = implode('', array_map('strval', $addressFields));
                    $addressHash = Tools::hash($addressToHash);

                    if ($cookie->kl_sms_consent === $addressHash) {
                        $consentAddress = $address;
                        break;
                    }
                }
            }

            // Get mobile on invoice address
            if (is_null($consentAddress)) {
                return;
            }

            $mobile = $address->phone_mobile ?? $address->phone ?? '';
            $isoCode = '';
            if (!empty($address->id_country)) {
                $country = new Country($address->id_country);
                if (Validate::isLoadedObject($country)) {
                    $isoCode = $country->iso_code;
                }
            }

            $attributes = ['mobile' => $mobile];

            $customer = new Customer((int)$params['order']->id_customer);
            if (Validate::isLoadedObject($customer)) {
                $attributes['email'] = $customer->email;
            }

            $this->sendSMSConsentRequestAPI($attributes, $isoCode);
        }
    }

    /**
     * Callback for hook that fires after MailAlert resource is created. Supports
     * Back in Stock subscription in Klaviyo.
     *
     * @param $params
     * @return void
     */
    public function handleActionObjectMailAlertAddAfter($params)
    {
        if ((int)$this->klaviyoModule->getConfigurationValueOrNull('KLAVIYO_BIS_ENABLED') !== 1) {
            return;
        }

        $mailAlertObject = $params['object'];
        // Klaviyo catalogs expect a constructed variant identifier of the
        // format {product_id}:{variant_id}, e.g. 19-3 or 21-0 (simple product).
        $constructedId =  "$mailAlertObject->id_product-$mailAlertObject->id_product_attribute";

        $api = new KlaviyoApiWrapper();
        try {
            $api->createBackInStockSubscription($mailAlertObject->customer_email, $constructedId, $mailAlertObject->id_shop, $mailAlertObject->id_lang);
        } catch (KlaviyoApiException $e) {
            $logger = $this->klaviyoModule->getService('klaviyops.prestashop_services.logger');
            $logger->error("{$e->getCode()} error while sending Back in Stock subscription event. {$e->getMessage()}");
        }
    }

    /**
     * Extract Customer object from hook params.
     *
     * @param array $hookParams
     * @return Customer|null
     */
    private function getCustomerFromHookParams(array $hookParams)
    {
        if (isset($hookParams['customer']) && $hookParams['customer'] instanceof CustomerCore) {
            return $hookParams['customer'];
        }

        if (isset($hookParams['newCustomer']) && $hookParams['newCustomer'] instanceof CustomerCore) {
            return $hookParams['newCustomer'];
        }

        return null;
    }

    /**
     * Subscribe mobile to a list
     *
     * @param  array $address
     * @param  string $isoCode
     * @return void
     */
    private function sendSMSConsentRequestAPI($address = [], $isoCode = '')
    {
        if (empty($address) || !is_array($address)) {
            return;
        }
        if (empty($address['mobile'])) {
            return;
        }

        try {
            $address['mobile'] = KlaviyoUtils::formatPhone($address['mobile'], $isoCode);

            $api = new KlaviyoApiWrapper();
            $api->subscribeSMSCustomer($address);
        } catch (KlaviyoApiException $e) {
            $logger = new LoggerService();
            $logger->log('error', self::ERROR_SUBSCRIBING_SMS_CUSTOMER . $address['mobile']);
        }
    }
}
