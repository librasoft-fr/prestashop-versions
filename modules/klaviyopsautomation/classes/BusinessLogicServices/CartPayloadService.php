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
use Product;
use ObjectModelCore;
use KlaviyoPs\Classes\BusinessLogicServices\CustomerPayloadService;

class CartPayloadService extends PayloadServiceInterface
{
    const SENSITIVE_KEYS = array('secure_key');

    /**
     * @inheritDoc
     */
    public static function buildPayload(ObjectModelCore $cart, $id_shop = null)
    {
        $cartProducts = $cart->getProducts();
        $hydratedCartProducts = array();

        foreach ($cartProducts as $item) {
            $productId = $item['id_product'];
            $productCategories = Product::getProductCategoriesFull($id_product = $productId);
            $hydratedProduct = array_merge(
                $item,
                array('categories' => $productCategories)
            );
            array_push($hydratedCartProducts, $hydratedProduct);
        }

        return array_merge(
            self::removeSensitiveKeys($cart, self::SENSITIVE_KEYS),
            array(
                'customer' => CustomerPayloadService::buildPayload(new Customer($cart->id_customer)),
                'line_items' => $hydratedCartProducts
            )
        );
    }
}
