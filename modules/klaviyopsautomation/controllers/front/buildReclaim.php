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

use KlaviyoPs\Classes\KlaviyoApiWrapper;
use KlaviyoPs\Classes\KlaviyoServices\CustomerEventService;
use KlaviyoPs\Classes\KlaviyoUtils;
use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoPs\Classes\PrestashopServices\ContextService;
use KlaviyoPs\Classes\PrestashopServices\CustomerService;
use KlaviyoPs\Classes\PrestashopServices\LoggerService;
use KlaviyoPs\KlaviyoPsAjaxModuleFrontController;
use KlaviyoV3Sdk\Exception\KlaviyoException;

/**
 * @property Klaviyops $module
 *
 * Class KlaviyoPsBuildReclaimModuleFrontController
 *
 * Available at /klaviyo/reclaim/build-reclaim. Fetch a cart from the db and reload it for the customer.
 */
class KlaviyoPsBuildReclaimModuleFrontController extends KlaviyoPsAjaxModuleFrontController
{
    /**
     * @inheritDoc
     */
    protected function ajaxProcess()
    {
        if (!Tools::getValue('email') || !Tools::getValue('cartId')) {
            $this->errors[] = 'Unable to send event, invalid email or cart ID.';
            return;
        }
        if (
            $this->sendStartedCheckoutEvent(
                (int) Tools::getValue('cartId'),
                Tools::getValue('email')
            )
        ) {
            $this->returnData[] = 'Sent Started Checkout event to Klaviyo.';
        } else {
            $this->errors[] = 'Failed to send Started Checkout event to Klaviyo.';
        }
    }

    /**
     * @inheritDoc
     */
    protected function isValidRequest()
    {
        if (Tools::getValue('token') !== Tools::getToken()) {
            $this->errors[] = 'Invalid request, token does not match.';
        }
        if (!filter_var(Tools::getValue('email'), FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid request, email address is not a valid email.';
        }
        return parent::isValidRequest();
    }

    // NOTE: Temporary function until we add webhooks at which point we'll use the CartPayloadService and then build
    // the event app-side.
    /**
     * @param $cartId int
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function sendStartedCheckoutEvent($cartId, $email)
    {
        try {
            /** @var KlaviyoApiWrapper $api */
            $api = $this->module->getService('klaviyops.klaviyo_api_wrapper');

            $shop = $this->context->shop;
            $shop_id = $shop->id;
            $cart = new Cart($cartId, $this->context->language->id);
            $cartLineItemsArray = KlaviyoUtils::buildCartLineItemsArray($cart);

            // NOTE: This hook fires on each step of the checkout process.
            // Cart IDs are unique in db so we can use as $event_id.
            $eventConfig = array(
                'event' => '$started_checkout',
                'customer_properties' => $this->buildCustomerPayload($email),
                'properties' => array(
                    '$event_id' => $cartId,
                    '$service' => KlaviyoValue::SERVICE,
                    'integration_key' => KlaviyoValue::SERVICE,
                    '$value' => (float) $cart->getOrderTotal(),
                    '$extra' => array(
                        'line_items' => $cartLineItemsArray['lineItems']
                    ),
                    'Language' => $this->context->language->id,
                    'CartID' => $cartId,
                    'Items' => $cartLineItemsArray['itemNames'],
                    'ItemCount' => $cartLineItemsArray['itemCount'],
                    'TotalDiscounts' => (float) number_format($this->getCartDiscountTotal($cart), 2),
                    'Categories' => $cartLineItemsArray['uniqueCategories'],
                    'Tags' => $cartLineItemsArray['uniqueTags'],
                    'ShopId' => $shop_id,
                    'ReclaimCartUrl' => KlaviyoUtils::buildReclaimCartUrl($cart),
                    'external_catalog_id' => KlaviyoUtils::formatKlaviyoCatalogIdentifier($shop_id, $this->context->language->id),
                ),
            );

            return $api->trackEvent($eventConfig);
        } catch (Exception $e) {
            /** @var LoggerService $logger */
            $logger = $this->module->getService('klaviyops.prestashop_services.logger');

            $logger->error("{$e->getCode()} error while sending Started Checkout event. {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Calculate total discount value for cart.
     *
     * @param $cart
     * @return float
     */
    private function getCartDiscountTotal($cart)
    {
        $discount_total = 0.0;

        $cart_rules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL);
        foreach ($cart_rules as $rule) {
            $discount_total += $rule['value_real'];
        }

        return $discount_total;
    }

    /**
     * @param string $email
     * @return Customer|null
     */
    private function getCustomerByEmail($email)
    {
        $customer = new Customer();
        $customer = $customer->getByEmail($email);

        if (!Validate::isLoadedObject($customer)) {
            return null;
        }

        return $customer;
    }

    /**
     * @param string $email
     * @return array
     * @throws KlaviyoException
     */
    private function buildCustomerPayload($email)
    {
        /** @var ContextService $contextService */
        $contextService = $this->module->getService('klaviyops.prestashop_services.context');
        /** @var CustomerService $customerService */
        $customerService = $this->module->getService('klaviyops.prestashop_services.customer');
        /** @var CustomerEventService $customerEventService */
        $customerEventService = $this->module->getService('klaviyops.klaviyo_service.customer_event_service');

        $customer = $this->getCustomerByEmail($email);

        // When the customer is not connected in the checkout, he set his mail in the first step
        // Then, started-checkout.js send a request to this controller with the mail
        // To keep this feature in the case where the customer has not yet an account
        // We need to build a normalized guest customer Object with the mail only
        // Then, CustomerEventService will build "restricted" data as guest customer
        if ($customer === null) {
            $customer = [
                'email' => $email,
            ];
        }

        $normalizedContext = $contextService->normalize(); // Current context
        $normalizedCustomer = $customerService->normalize(
            $customer,
            $normalizedContext
        );

        return $customerEventService->buildPayload($normalizedCustomer);
    }
}

class KlaviyoPsAutomationBuildReclaimModuleFrontController extends KlaviyoPsBuildReclaimModuleFrontController
{
}
