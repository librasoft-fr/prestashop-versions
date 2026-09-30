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

use Configuration;
use PrestaShop\PrestaShop\Adapter\Entity\Context;
use Db;
use Image;
use ImageType;
use Language;
use Link;
use ObjectModelCore;
use PrestaShop\PrestaShop\Adapter\Entity\Address;
use PrestaShop\PrestaShop\Adapter\Entity\Currency;
use PrestaShop\PrestaShop\Adapter\Entity\Pack;
use PrestaShop\PrestaShop\Adapter\Product\PriceCalculator;
use Product;
use ProductCore;
use PrestaShop\PrestaShop\Adapter\Entity\Shop;
use Tag;
use http\Exception\InvalidArgumentException;


class ProductPayloadService extends PayloadServiceInterface
{
  /** @var int Value to check against for product stock availability. */
    const CHECK_QTY_VALUE_ONE = 1;

  /**
   * @inheritDoc
   */
    public static function buildPayload(ObjectModelCore $product, $id_shop = null)
    {
        if (!is_null($id_shop)) {
            Context::getContext()->shop = new Shop((int) $id_shop);
            Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
        }
        $currency = new Currency(null, null, $id_shop);
        $shopDefaultCurrency = $currency->getDefaultCurrency();

        return array_merge(
            self::objectToArray($product),
            array(
            'categories' => self::buildMultiLangCategories($product->id),
            'url' => self::buildProductPageUrls($product, $id_shop),
            'imageUrl' => self::buildProductImageUrls($product),
            'tags' => self::getProductTagsArray($product->id),
            'in_stock' => $product->checkQty(self::CHECK_QTY_VALUE_ONE),
            'quantity' => Product::getQuantity($product->id),
            'currency' => $shopDefaultCurrency->iso_code,
            'tax_incl_price' => self::getPriceStatic(
                $id_shop,
                $product->id,
                $shopDefaultCurrency->id,
                true,
                true
            ),
            'discount_excl_price' => self::getPriceStatic(
                $id_shop,
                $product->id,
                $shopDefaultCurrency->id,
                true,
                false
            ),
            'products_in_pack' => self::buildProductsInPack($product->id, Configuration::get('PS_LANG_DEFAULT'))
            )
        );
    }

  /**
   * Get all associated products in the pack for a product.
   *
   * @param $product_id
   * @return array | null
   */
    public static function buildProductsInPack($product_id, $lang_id)
    {
        if (!Pack::isPack($product_id)) {
            return null;
        }

        $result = Pack::getItems($product_id, $lang_id);

        $pack_products = [];
        foreach ($result as $p) {
            $pack_products[] = [
            'id' => $p->id,
            'variant_id' => (int)$p->id_pack_product_attribute
            ];
        }
        return $pack_products;
    }


  /**
   * Get all categories for each available language for a product.
   *
   * @param $product_id
   * @return array
   * @throws \PrestaShopDatabaseException
   */
    public static function buildMultiLangCategories($product_id)
    {
        $results = Db::getInstance()->executeS(
            'SELECT DISTINCT id_lang
            FROM ' . _DB_PREFIX_ . 'product_lang
            WHERE id_product = ' . (int) $product_id . ';'
        );

      // If we don't get a result just use the default language and format like we would for a result from DB.
        if (!$results || empty($results)) {
            $results = array(
            array('id_lang' => Configuration::get('PS_LANG_DEFAULT'))
            );
        }

        $returnCategories = array();
        foreach ($results as $result) {
            $lang_id = $result['id_lang'];
            $returnCategories[$lang_id] = Product::getProductCategoriesFull($product_id, $lang_id);
        }

        return $returnCategories;
    }

  /**
   * Get indexed array of category names for a Product.
   *
   * @param $productId
   * @param $langId
   * @return array
   */
    public static function getCategoryNamesForProduct($productId, $langId)
    {
        return array_values(
            array_map(
                function ($cat) {
                    return $cat['name'];
                },
                Product::getProductCategoriesFull($productId, $langId)
            )
        );
    }

  /**
   * Build urls for product pages for multi-language support.
   *
   * Doing this to maintain consistent structure returned for other multi-lang product properties. The getProductLink
   * method defaults to context language if one is not provided (for a webservice query that will be default lang) and
   * so returns the wrong url. This allows us to process mutli-lang the same as other properties app-side.
   *
   * @param ProductCore $product
   * @param int $id_shop
   * @param int $comboId
   * @return array|string
   * @throws \PrestaShopException
   */
    public static function buildProductPageUrls(ProductCore $product, $id_shop, $comboId = null)
    {
        $languages = Language::getLanguages($active = false);

      // If there is only one language return a single string.
        if (count($languages) == 1) {
            return self::getProductUrl($product->id, $languages[0]['id_lang'], $id_shop, $comboId);
        }

        $productPageUrls = array();
        foreach ($languages as $language) {
            $langId = $language['id_lang'];
            $productPageUrls[$langId] = self::getProductUrl($product->id, $langId, $id_shop, $comboId);
        }
        return $productPageUrls;
    }

  /**
   * Build link to product page.
   *
   * @param $productId
   * @param $id_lang
   * @param int|null $comboId This can be 0 as getProductLink handles it fine.
   * @return string
   */
    public static function getProductUrl($productId, $langId, $shopId, $comboId = null)
    {
        $link = new Link();
        return $link->getProductLink($productId, null, null, null, $langId, $shopId, $comboId);
    }

  /**
   * If a product object has a language specified then various language specific instance variables (ex. link_rewrite)
   * will return an associative array with a string for each language. If no language is specified then these
   * variables return a string.
   *
   * Product queries will not have a language defined and we want to return an array of images based on the language
   * link_rewrite values for syncing to the Klaviyo catalog. Whereas a product for a Viewed Product event will have a
   * language defined based on the context.
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
    public static function buildProductImageUrls(ProductCore $product)
    {
        $productImageUrls = null;

        $coverImageRow = Image::getCover($product->id);
        if (!is_array($coverImageRow) || !isset($coverImageRow['id_image'])) {
            return $productImageUrls;
        }

        $imageId = $coverImageRow['id_image'];

        $linkRewrite = $product->link_rewrite;
        if (is_array($linkRewrite)) {
            foreach ($linkRewrite as $langId => $linkRewriteValue) {
                $productImageUrls[$langId] = self::buildImageUrl($linkRewriteValue, $imageId);
            }
        } else {
            $productImageUrls = self::buildImageUrl($linkRewrite, $imageId);
        };

        return $productImageUrls;
    }

  /**
   * Build image url for product using the image ID and link_rewrite slug.
   *
   * @param $linkRewrite
   * @param $imageId
   * @return string
   */
    public static function buildImageUrl($linkRewrite, $imageId)
    {
        $link = new Link();
        $imageTypeName = ImageType::getFormattedName('home');

        $imagePath = $link->getImageLink($linkRewrite, $imageId, $imageTypeName);
        return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $imagePath;
    }

  /**
   * Get array of tag values for specific language or an array of arrays for each language for a product.
   *
   * @param $product_id
   * @return array|array[arrays]
   */
    public static function getProductTagsArray($product_id, $id_lang = null)
    {
        $tags = Tag::getProductTags($product_id);
        if (!$tags || !array_key_exists($id_lang, $tags)) {
            return array();
        }
        return $id_lang ? $tags[$id_lang] : $tags;
    }


  /**
   * Get the price of the product, which price returned depends on which arguments are provided. idShop, idProduct, and idCurrency will always be passed.
   *
   * includeTax -> returns price including tax
   * includeDiscount -> returns the discounted price
   * idProductAttribute -> if included returns the price +/- for the specific combination, instead of the parent product price.
   *
   * @param $idShop
   * @param $idProduct
   * @param $idCurrency
   * @param $includeTax
   * @param $includeDiscount
   * @param $idProductAttribute
   * @return float
   */
    public static function getPriceStatic($idShop, $idProduct, $idCurrency, $includeTax, $includeDiscount, $idProductAttribute = null): float
    {
        $requiredFields = ['idShop', 'idProduct', 'idCurrency', 'includeTax', 'includeDiscount'];
        foreach ($requiredFields as $fieldname) {
            if (is_null(${$fieldname})) {
                throw new \InvalidArgumentException($fieldname . " cannot be null.");
            }
        }

        $address = Address::initialize(null, true);
        $id_country = $address->id_country;
        $id_state = $address->id_state;
        $zipcode = $address->postcode;

        $priceCalc = new PriceCalculator();
        $specificPrice = null;
        $price =  $priceCalc->priceCalculation(
            $idShop,
            $idProduct,
            $idProductAttribute,
            $id_country,
            $id_state,
            $zipcode,
            $idCurrency, // for currency-specific discounts
            null,
            null,
            $includeTax,
            2, // avoid extra rounding
            null,
            $includeDiscount,
            true, // always include ecotax if specified
            $specificPrice,
            $includeDiscount // include the discounts declared as group reductions
        );
        return $price;
    }
}
