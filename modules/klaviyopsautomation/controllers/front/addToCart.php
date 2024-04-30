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

use KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService;
use KlaviyoPs\Classes\KlaviyoUtils;
use KlaviyoPs\KlaviyoPsAjaxModuleFrontController;

class KlaviyoPsAddToCartModuleFrontController extends KlaviyoPsAjaxModuleFrontController
{
    /**
     * @inheritDoc
     */
    protected function ajaxProcess()
    {
        $payload = $this->buildAddedToCartPayload();

        // If we cannot build a cart because we don't have a cart in context or if the cart is actually empty.
        if (!isset($payload['ItemCount']) || $payload['ItemCount'] == 0) {
            $this->errors[] = 'Invalid or empty cart.';
        } else {
            $this->returnData = $payload;
        }
    }

    /**
     * Build payload for Added to Cart event.
     *
     * @return array
     */
    private function buildAddedToCartPayload()
    {
        $cartId = $this->context->cart->id;
        $cartObject = new Cart($cartId);
        $cartLineItemsArray = KlaviyoUtils::buildCartLineItemsArray($cartObject);

        return array_merge(
            $this->getAddedItem(),
            array(
                '$value' => $cartObject->getOrderTotal(),
                'ItemNames' => $cartLineItemsArray['itemNames'],
                'Items' => $cartLineItemsArray['lineItems'],
                'ItemCount' => $cartLineItemsArray['itemCount'],
                'Categories' => $cartLineItemsArray['uniqueCategories'],
                'Tags' => $cartLineItemsArray['uniqueTags'],
                'ReclaimCartUrl' => KlaviyoUtils::buildReclaimCartUrl($cartObject),
            )
        );
    }

    /**
     * Get details of most recently added item to a cart.
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getAddedItem()
    {
        // Cart does not have getLastProduct method if no context.
        try {
            $itemDetails = $this->context->cart->getLastProduct();
        } catch (Exception $e) {
            return array();
        }

        // getLastProduct() can return false if item is not found.
        if (!$itemDetails) {
            return array();
        }

        $productId = $itemDetails['id_product'];
        $langId = $this->context->language->id;
        $shopId = $this->context->shop->id;
        $product = new Product($itemDetails['id_product'], $full = false, $id_lang = $langId, $id_shop = $shopId);

        return array(
            'AddedItemCategories' => ProductPayloadService::getCategoryNamesForProduct($productId, $langId),
            'AddedItemDescription' => strip_tags($itemDetails['description_short']),
            'AddedItemImageURL' => KlaviyoUtils::getProductImageLink($productId, $itemDetails['id_product_attribute'], $shopId, $langId),
            'AddedItemPrice' => (float) $itemDetails['price'],
            'AddedItemPriceInclTax' => $product->getPrice(),
            'AddedItemProductID' => (int) $productId,
            'AddedItemProductName' => $itemDetails['name'],
            'AddedItemSKU' => $itemDetails['reference'],
            'AddedItemTags' => ProductPayloadService::getProductTagsArray($productId, $langId),
            'AddedItemURL' => ProductPayloadService::getProductUrl($product, $langId, $shopId),
        );
    }
}

class KlaviyoPsAutomationAddToCartModuleFrontController extends KlaviyoPsAddToCartModuleFrontController
{
}
