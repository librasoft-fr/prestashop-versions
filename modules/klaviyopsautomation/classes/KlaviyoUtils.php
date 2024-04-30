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

use Cart;
use Configuration;
use DateTime;
use Context;
use Db;
use Exception;
use Image;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\BusinessLogicServices\OrderPayloadService;
use Link;
use Product;
use KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService;
use Validate;

/**
 * Class KlaviyoUtils is a collection of utility methods reused across multiple KlaviyoPs module classes.
 * @package KlaviyoPs\Classes
 */
class KlaviyoUtils
{
    /**
     * List of rewrite rules for Authorization header fix.
     * @var string[]
     */
    const HTACCESS_REWRITE_RULES = [
        'SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1',
        'RewriteCond %{HTTP:Authorization} ^(.*)',
        'RewriteRule . - [E=HTTP_AUTHORIZATION:%1]',
    ];

    /**
     * Get full url for product image based on attribute ID, otherwise get cover image.
     *
     * @param $productId
     * @return string
     */
    public static function getProductImageLink($product_id, $product_attribute_id, $shop_id, $lang_id)
    {
        if ($product_attribute_id) {
            $image = Image::getBestImageAttribute(
                $shop_id,
                $lang_id,
                $product_id,
                $product_attribute_id
            );
        }
        // Some product attributes don't correspond to a different image e.g. size. Handle that here as well.
        if (!isset($image) || !$image) {
            $image = Image::getCover($product_id);
        }

        if (is_array($image) && isset($image['id_image'])) {
            $image = new Image($image['id_image']);
            return _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . ".jpg";
        }
    }

    /**
     * Build object containing values compiled from and including cart line items e.g. unique categories.
     *
     * @param Cart $cart
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function buildCartLineItemsArray($cart)
    {
        $langId = $cart->id_lang;
        $shopId = $cart->id_shop;

        // Define total cart variables.
        $itemCount = 0;
        $itemNames = array();
        $lineItems = array();
        $productCategories = array();
        $productTags = array();

        $products = $cart->getProducts();
        foreach ($products as $product) {
            $productId = $product['id_product'];
            $productObj = new Product($productId, false, $id_lang = $langId, $id_shop = $shopId);

            foreach (Product::getProductCategoriesFull($id_product = $productId, $id_lang = $langId) as $category) {
                $category_name = $category['name'];
                if (!in_array($category_name, $productCategories)) {
                    $productCategories[] = $category_name;
                }
            };

            $tags = ProductPayloadService::getProductTagsArray($productId, $langId);
            foreach ($tags as $tag) {
                if (!in_array($tag, $productTags)) {
                    $productTags[] = $tag;
                }
            }

            $itemNames[] = $product['name'];
            $lineItems[] = array(
                'Image' => KlaviyoUtils::getProductImageLink($productId, $product['id_product_attribute'], $shopId, $langId),
                'ProductURL' => ProductPayloadService::getProductUrl($productId, $langId, $shopId),
                'ProductID' => $productId,
                'Price' => number_format($product['price'], 2),
                'Quantity' => $product['quantity'],
                'ProductUniqueID' => $product['unique_id'],
                'ProductInfo' => $product,
                'Tags' => $tags,
            );

            $itemCount += (int) $product['quantity'];
        };

        return array(
            'lineItems' => $lineItems,
            'itemNames' => $itemNames,
            'itemCount' => $itemCount,
            'uniqueCategories' => $productCategories,
            'uniqueTags' => $productTags
        );
    }

    /**
     * Build a token to secure the recovery procedure of a cart
     *
     * @param int $idCart
     * @return string
     */
    public static function buildRecoverCartToken($idCart)
    {
        $key = _COOKIE_KEY_;
        return md5("{$key}recover_cart_{$idCart}");
    }

    /**
     * Build url to reclaim cart.
     *
     * @param Cart $cart
     * @return string
     */
    public static function buildReclaimCartUrl(Cart $cart)
    {
        $context = Context::getContext();

        $link = $context->link;
        if ($link === null) {
            $link = new Link();
        }

        $idCart = (int)$cart->id;
        $idShop = (int)$cart->id_shop;
        $idLang = (int)$cart->id_lang;
        $token = self::buildRecoverCartToken($idCart);

        return $link->getModuleLink(
            'klaviyo',
            'reclaim',
            [
                'id_cart' => $idCart,
                'id_shop' => $idShop,
                'token' => $token,
            ],
            null,
            ($idLang > 0) ? $idLang : null,
            ($idShop > 0) ? $idShop : null
        );
    }

    /**
     * Get order status mappings for all stores where configured.
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public static function getAllOrderStatusMaps()
    {
        $sql = 'SELECT `id_shop`, `value`, `date_add`, `date_upd` FROM ' . _DB_PREFIX_ . 'configuration WHERE `name` = "KLAVIYO_ORDER_STATUS_MAP"';
        $result = Db::getInstance()->ExecuteS($sql);

        $statusMaps = array();
        foreach ($result as $statusMap) {
            $statusMaps[$statusMap['id_shop']] = array(
                'map' => json_decode($statusMap['value'], true),
                'date_add' => $statusMap['date_add'],
                'date_upd' => $statusMap['date_upd'],
            );
        }
        return $statusMaps;
    }

    /**
     * Returns the corresponding event type for an order state e.g. klaviyops-statuses-refunded.
     *
     * @param int $OrderStateId
     * @param int|null $shopId
     * @return string|null
     */
    public static function getMappedOrderStatusValue(
        $OrderStateId,
        $shopId = null
    ) {
        $json = Configuration::get(
            'KLAVIYO_ORDER_STATUS_MAP',
            null,
            null,
            $shopId
        );

        $statusMap = null;
        if ($json !== false) {
            $statusMap = json_decode($json, true);
        }

        if (!$statusMap) {
            $statusMap = OrderPayloadService::ORDER_STATUS_MAP_DEFAULT;
        }

        $mappedStatus = null;
        foreach ($statusMap as $eventType => $statusIds) {
            if (in_array($OrderStateId, $statusIds)) {
                $mappedStatus = $eventType;
                break;
            }
        }

        return $mappedStatus;
    }

    /**
     * Converts numeric value to formatted string. Non-numeric return "0.00".
     *
     * @param $price
     * @return string
     */
    public static function formatPrice($price)
    {
        return number_format(is_numeric($price) ? $price : 0, 2, '.', '');
    }

    /**
     * Format date from PrestaShop format to timestamp format to send it to Klaviyo
     *
     * @param string|null $date
     * @param int|null $defaultTime
     * @return int|null
     */
    public static function formatDate($date, $defaultTime = null)
    {
        try {
            if (
                $date === null ||
                !Validate::isDate($date) ||
                $date === '0000-00-00'
            ) {
                throw new KlaviyoException();
            }

            $date = new DateTime($date);

            $minimumDate = new DateTime('2000-01-01');
            if ($minimumDate > $date) {
                throw new KlaviyoException();
            }

            return $date->getTimestamp();
        } catch (Exception $e) {
            return $defaultTime;
        }
    }

    /**
     * Helper function to determine whether an array has non-integer keys.
     *
     * @param array $array
     * @return bool
     */
    public static function hasStringKeys(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Whether to warn a customer about the potential for php running as CGI to
     * cause API authentication issues from Authorization header being stripped.
     *
     * @return bool
     */
    public static function shouldWarnCGI()
    {
        // Check htaccess for HTTP_AUTHORIZATION
        $path = _PS_ROOT_DIR_ . '/.htaccess';
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);
        foreach (self::HTACCESS_REWRITE_RULES as $rule) {
            if (is_int(strpos($content, $rule))) {
                return false;
            }
        }

        // Check if php is CGI
        $sapi_type = php_sapi_name();
        if (!is_string($sapi_type)) {
            return false;
        }
        // strpos will either return the position of the substring or false.
        // If the value is an int, we know cgi is in the sapi name and we should warn the user.
        return is_int(strpos($sapi_type, 'cgi'));
    }
}
