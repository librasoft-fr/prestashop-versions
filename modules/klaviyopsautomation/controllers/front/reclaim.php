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

use KlaviyoPs\Classes\KlaviyoUtils;

/**
 * Class KlaviyoPsReclaimModuleFrontController
 *
 * Available at /klaviyo/reclaim/cart. Fetch a cart from the db and reload it for the customer.
 */
class KlaviyoPsReclaimModuleFrontController extends ModuleFrontController
{
    /**
     * KlaviyoPsReclaimModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Initialize the controller. Get cart ID from query string and update context. Redirect
     * to cart page while persisting any other query string parameters in request.
     */
    public function init()
    {
        $query_params = Tools::getAllValues();

        $cart_id = $query_params['id_cart'];
        $token = $query_params['token'];

        // Query string keys that we don't want to persist in the redirect.
        $exclude_keys = [
            'fc',
            'module',
            'controller',
            'id_cart',
            'id_lang',
            'isolang',
            'token',
        ];
        $persist_keys = ['action' => 'show'];

        foreach ($query_params as $key => $value) {
            if (!in_array($key, $exclude_keys) && Validate::isUrl($key) && Validate::isUrl($value)) {
                $persist_keys[Tools::safeOutput($key)] = Tools::safeOutput($value);
            }
        }

        if (
            $cart_id &&
            $token &&
            $token === KlaviyoUtils::buildRecoverCartToken($cart_id)
        ) {
            // This is intentionally not checked against the user's session to
            // allow for cross-device compatibility when recovering a cart.
            $reclaimed_cart = new Cart($cart_id);
            $customer_cart = new Customer((int)$reclaimed_cart->id_customer);

            // If the cart is associated to a customer
            // We can use PrestaShop's native cart recovery system
            // Otherwise we cant't. In this case, we have to implement it ourselves
            // See : FrontController::recoverCart
            if (Validate::isLoadedObject($customer_cart)) {
                $persist_keys['recover_cart'] = $reclaimed_cart->id;
                $persist_keys['token_cart'] = $token;
            } else {
                $this->context->cookie->id_cart = $reclaimed_cart->id;
            }
        }

        $link = $this->context->link;
        if ($link === null) {
            $link = new Link();
        }

        $redirectLink = $link->getPageLink(
            'cart',
            null,
            null,
            $persist_keys
        );

        Tools::redirect($redirectLink);
    }
}

class KlaviyoPsAutomationReclaimModuleFrontController extends KlaviyoPsReclaimModuleFrontController
{
}
