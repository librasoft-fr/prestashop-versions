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

namespace KlaviyoPs\Classes\PrestashopServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ArrayObject;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService;
use Product;
use Validate;

class ProductService
{
    /**
     * Normalizing data from PrestaShop allows to use them in a uniform and secure way.
     * To use the methods of this class, start by normalizing data.
     *
     * @param Product|array $product
     * @return ArrayObject
     */
    public function normalize($product)
    {
        $productObject = null;

        if (is_object($product)) {
            $productObject = $product;

            $product = (array)$product;
            $product['id_product'] = $productObject->id;
            $product['cache_object'] = $productObject;

            unset($product['id']);
        }

        if (!isset($product['id_product'])) {
            $product['id_product'] = 0;
        }

        if (!isset($product['id_product_attribute'])) {
            $product['id_product_attribute'] = 0;
        }

        if (!isset($product['id_customization'])) {
            $product['id_customization'] = 0;
        }

        $product['id_product'] = (int) $product['id_product'];
        $product['id_product_attribute'] = (int)$product['id_product_attribute'];
        $product['id_customization'] = (int)$product['id_customization'];

        return new ArrayObject($product);
    }

    /**
     * Get Product object from normalized data
     *
     * @param ArrayObject $product
     * @return Product
     * @throws KlaviyoException
     */
    public function getObject(ArrayObject $product)
    {
        if (!$product->offsetExists('cache_object')) {
            $product['cache_object'] = new Product($product['id_product']);

            if (!Validate::isLoadedObject($product['cache_object'])) {
                throw new KlaviyoException('Product is not valid');
            }
        }

        return $product['cache_object'];
    }

    /**
     * @param ArrayObject $product
     * @param ArrayObject $context
     * @return array
     */
    public function getCategoryNames(
        ArrayObject $product,
        ArrayObject $context
    ) {
        if (!$product->offsetExists('cache_category_names')) {
            $product['cache_category_names'] = ProductPayloadService::getCategoryNamesForProduct(
                $product['id_product'],
                $context['id_lang']
            );
        }

        return $product['cache_category_names'];
    }

    /**
     * @param ArrayObject $product
     * @param ArrayObject $context
     * @return array
     */
    public function getTags(
        ArrayObject $product,
        ArrayObject $context
    ) {
        if (!$product->offsetExists('cache_tags_array')) {
            $product['cache_tags_array'] = ProductPayloadService::getProductTagsArray(
                $product['id_product'],
                $context['id_lang']
            );
        }

        return $product['cache_tags_array'];
    }

    /**
     * @param ArrayObject $product
     * @return bool
     */
    public function isVirtual(ArrayObject $product)
    {
        if (!isset($product['is_virtual'])) {
            return false;
        }

        return (bool)$product['is_virtual'];
    }
}
