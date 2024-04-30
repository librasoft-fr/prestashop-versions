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

use CartRule;
use Customer;
use ObjectModelCore;
use OrderCore;
use Product;
use KlaviyoPs\Classes\BusinessLogicServices\PayloadServiceInterface;
use KlaviyoPs\Classes\KlaviyoUtils;
use Order;

class OrderPayloadService extends PayloadServiceInterface
{
    const SENSITIVE_KEYS = array('secure_key', 'smarty');

    const ORDER_STATUS_MAP_DEFAULT = [
        'klaviyops-statuses-placed' => ['2'],
        'klaviyops-statuses-refunded' => ['7'],
        'klaviyops-statuses-canceled' => ['6'],
        'klaviyops-statuses-fulfilled' => ['4'],
    ];

    /**
     * @inheritDoc
     */
    public static function buildPayload(ObjectModelCore $order, $id_shop = null)
    {
        // Method getProducts() returns an associative array and we want indexed.
        $orderProducts = array_values($order->getProducts());
        $hydratedOrderProducts = array();

        foreach ($orderProducts as $item) {
            $productId = $item['id_product'];
            $productCategories = Product::getProductCategoriesFull($id_product = $productId);
            $hydratedProduct = array_merge(
                $item,
                array(
                    'categories' => $productCategories,
                    'image_url' => KlaviyoUtils::getProductImageLink(
                        $productId,
                        $item['product_attribute_id'],
                        $order->id_shop,
                        $order->id_lang
                    ),
                    'tags' => ProductPayloadService::getProductTagsArray($productId, $order->id_lang),
                )
            );
            array_push($hydratedOrderProducts, $hydratedProduct);
        }

        return array_merge(
            self::removeSensitiveKeys($order, self::SENSITIVE_KEYS),
            array(
                'customer' => CustomerPayloadService::buildPayload(new Customer($order->id_customer)),
                'line_items' => $hydratedOrderProducts,
                'date_add_utc' => self::convertDateStringToUTC($order->date_add),  // UTC timestamp for Klaviyo events
                'date_upd_utc' => self::convertDateStringToUTC($order->date_upd),  // UTC timestamp for Klaviyo events
                'applied_cart_rule_codes' => self::getAppliedCartRuleCodes($order),
                'mapped_order_status' => self::getMappedOrderStatusValue($order),
            )
        );
    }

    /**
     * Get discount codes from applied cart rules.
     *
     * @param OrderCore $order
     * @return array
     */
    protected static function getAppliedCartRuleCodes(OrderCore $order)
    {
        $discount_codes = array();

        $cart_rules = $order->getCartRules();
        foreach ($cart_rules as $rule) {
            $cart_rule = new CartRule($rule['id_cart_rule']);
            $discount_codes[] = $cart_rule->code;
        }

        return $discount_codes;
    }

    /**
     * Returns the corresponding event type for an order's current state e.g. klaviyops-statuses-refunded.
     *
     * @param Order $order
     * @return string|null
     */
    public static function getMappedOrderStatusValue($order)
    {
        return KlaviyoUtils::getMappedOrderStatusValue(
            $order->current_state,
            $order->id_shop
        );
    }
}
