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

use Language;
use PrestaShop\PrestaShop\Adapter\Entity\Cache;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\Image;
use PrestaShop\PrestaShop\Adapter\Entity\Link;
use PrestaShop\PrestaShop\Adapter\Entity\Product;
use ProductCore;
use Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CombinationPayloadService extends PayloadServiceInterface
{
    /**
     * If a product object has a language specified then various language specific instance variables (ex. link_rewrite)
     * will return an associative array with a string for each language. If no language is specified then these
     * values return a string.
     *
     * @param ProductCore $product
     * @return array|string
     * With language:
     * array(
     *     '1' => 'http://prestashop-17.local:8888/1-home_default/hummingbird-printed-t-shirt.jpg',
     *     '2' => 'http://prestashop-17.local:8888/1-home_default/hummingbird-printed-t-shirt.jpg'
     * )
     *
     * Without language:
     * 'http://prestashop-17.local:8888/1-home_default/hummingbird-printed-t-shirt.jpg'
     */
    public static function buildComboImageUrls(ProductCore $product, int $id_product_attribute, int $id_shop)
    {
        $comboImageUrls = null;
        $imageId = self::getDefaultImage($product->id, $id_product_attribute, $id_shop);
        if (!$imageId) {
            return $comboImageUrls;
        }

        $linkRewrite = $product->link_rewrite;
        if (is_array($linkRewrite)) {
            foreach ($linkRewrite as $langId => $linkRewriteValue) {
                $comboImageUrls[$langId] = self::buildImageUrl($linkRewriteValue, $imageId);
            }
        } else {
            $comboImageUrls = self::buildImageUrl($linkRewrite, $imageId);
        };

        return $comboImageUrls;
    }

    /**
     * Returns the default image for a combination. The default image is distinct from the cover image of a product.
     * Zero, one or many images can be selected for a combination. This logic to select the default image mimics the
     * description of behavior used in the product combination.
     *
     * @param int $idProduct Product ID
     * @param int $idProductAttribute Product Attribute ID
     * @param int $idShop Shop ID
     *
     * @return array|false Images or false if cover image not found.
     */
    public static function getDefaultImage(int $idProduct, int $idProductAttribute, int $idShop)
    {
        $lookup = self::getCombinationImageLookup($idProduct, $idShop);
        if (array_key_exists($idProductAttribute, $lookup)) {
            return $lookup[$idProductAttribute];
        }

        return self::getDefaultImageIdForProduct($idProduct, $idShop);
    }

    /**
     * For combinations that do not have a selected image PrestaShop will use the lowest (preferred) position image on
     * the product as the default image.
     *
     * @param int $idProduct
     * @param int $idShop
     * @return mixed
     */
    public static function getDefaultImageIdForProduct(int $idProduct, int $idShop)
    {
        $cacheId = 'CombinationPayloadService::getDefaultImageIdForProduct_' . $idProduct . '_' . $idShop;
        if (!Cache::isStored($cacheId)) {
            // Query for default image on product
            $shopFilter = ' AND ims.`id_shop` = ' . (int) $idShop;
            $sql = 'SELECT i.`id_image` FROM `' . _DB_PREFIX_ . 'image` i';
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` ims ON (i.`id_image` = ims.`id_image`)';
            $sql .= ' WHERE i.`id_product` = ' . (int) $idProduct . $shopFilter;
            $sql .= ' ORDER BY i.`position` ASC';

            // getValue will return the first record.
            $imageId = Db::getInstance()->getValue($sql);
            Cache::store($cacheId, $imageId);

            return $imageId;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Fetches selected images for all combinations in a product. If a combination does not have an image selected no
     * results will be returned for that combination in the result. If a combination has multiple images selected we
     * will take the lowest (preferred) position.
     *
     * @param int $idProduct
     * @param int $idShop
     * @return array
     */
    public static function getCombinationImageLookup(int $idProduct, int $idShop)
    {
        $cacheId = 'CombinationPayloadService::getCombinationImageLookup_' . $idProduct . '_' . $idShop;
        if (!Cache::isStored($cacheId)) {
            // Query for all selected images for combinations of a product.
            $shopFilter = ' AND ims.`id_shop` = ' . (int) $idShop;
            $sql = 'SELECT ai.`id_product_attribute`, i.`id_image`, i.`position`
            FROM `' . _DB_PREFIX_ . 'product_attribute_image` ai';
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image` i on (ai.`id_image` = i.`id_image`)';
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` ims ON (ai.`id_image` = ims.`id_image`)';
            $sql .= ' WHERE ims.`id_product` = ' . (int) $idProduct . $shopFilter;
            $sql .= ' ORDER BY i.`position` ASC';

            $rows = Db::getInstance()->executeS($sql);

            // Determine lowest (preferred) position image for each combination returned from query above.
            $combinationImageLookup = [];
            foreach ($rows as $row) {
                // Variables extracted: $id_product_attribute, $id_image, $position
                extract($row);

                if (!array_key_exists($id_product_attribute, $combinationImageLookup)) {
                    $combinationImageLookup[$id_product_attribute] = $id_image;
                }
            }

            Cache::store($cacheId, $combinationImageLookup);

            return $combinationImageLookup;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Build image url for product using the image ID and link_rewrite slug.
     *
     * @param string $linkRewrite
     * @param int $imageId
     * @return string
     */
    public static function buildImageUrl(string $linkRewrite, int $imageId)
    {
        return ProductPayloadService::buildImageUrl($linkRewrite, $imageId);
    }


    /**
     * Build link to product page.
     *
     * @param $productId
     * @param $comboId
     * @param $id_lang
     * @return array|string
     */
    public static function getComboUrl(int $productId, int $comboId, int $shopId)
    {
        $product = new Product($productId, false, null, $shopId);
        return ProductPayloadService::buildProductPageUrls($product, $shopId, $comboId);
    }

    public static function getCombinationName(ProductCore $product, int $id_product_attribute)
    {
        // Get all language IDs
        $languages = Language::getLanguages(false, false, true);

        $combinationNames = [];
        foreach ($languages as $id_lang) {
            $sql = '
			SELECT al.`name` AS attribute_name
			FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac
			LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
			LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
			LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
			LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $id_lang . ')
			LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
			' . Shop::addSqlAssociation('product_attribute', 'pa') . '
			WHERE pac.`id_product_attribute` = ' . (int) $id_product_attribute;

            $result = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql);

            $formattedNames = '';
            if ($result) {
                foreach ($result as $row) {
                    $formattedNames .= $row['attribute_name'] . ', ';
                }
            }
            $formattedNames = rtrim($formattedNames, ', ');

            $combinationNames[(string) $id_lang] = $product->name[$id_lang] . ' ' . $formattedNames;
        }

        return  count($combinationNames) === 1 ? array_values($combinationNames)[0] : $combinationNames;
    }
}
