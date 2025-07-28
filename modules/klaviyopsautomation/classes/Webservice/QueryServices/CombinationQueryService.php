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

namespace KlaviyoPs\Classes\Webservice\QueryServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Cache;
use Db;
use DbQuery;
use KlaviyoPs\Classes\BusinessLogicServices\CombinationPayloadService;
use KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService;
use PrestaShop\PrestaShop\Adapter\Entity\Currency;
use Product;
use StockAvailable;
use WebserviceException;
use FileLogger;
use Shop;

class CombinationQueryService
{
    use QueryServiceTrait;

    public function __construct()
    {
        $this->logger = new FileLogger(0);
    }

    /**
     *  Retrieves a page of Combination data corresponding to the input product ids.
     *
     * @param $shop int Id of shop to scope req
     * @param $productIds array Requested product ids
     * @param $limit int Max number of results to fetch
     * @param $cursor string Optional cursor for queryset
     * @return array|bool|\mysqli_result|\PDOStatement|resource|null
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function getCombinationPage($shop, $productIds, $limit, $cursor): array
    {
        $query = new DbQuery();
        $query->from('product_attribute_shop', 'pas');
        $query->select('`pas`.id_product, `pas`.id_product_attribute, `pas`.id_shop');
        $query->where('`pas`.id_shop = ' . (int) $shop);
        $query->where('`pas`.id_product IN' . '(' . implode(',', $productIds) . ')');
        $query->limit($limit);
        $query->orderBy('`pas`.id_product DESC, `pas`.id_product_attribute DESC');

        if ($cursor) {
            // expand the cursor
            foreach ($cursor as $key => $value) {
                $query->where(sprintf('`%s` <= %d', $key, $value));
            }
        }
        $res = Db::getInstance()->executeS($query->build());      //TODO: optimize fields in query
        return ($res ?: array());
    }


    /**
     *  Normalizes row data for db query for combinations. Hydrates additional fields
     *  `parent_id`, `id`, `quantity`, `price`, `url`, `image_full_url`.
     *
     * @param array $comboProducts List of combination objects from database query
     * @param int $id_shop
     * @return array List of normalized Combinations fields for webservice response
     */
    public static function normalizeCombinationsData(array $comboProducts, int $id_shop): array
    {

        $hydratedComboProducts = array();
        $hydratedProduct = [];

        Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
        $currency = new Currency(null, null, $id_shop);
        $shopDefaultCurrency = $currency->getDefaultCurrency();

        if (!$comboProducts) {
            return $hydratedComboProducts;
        }
        /*
         *  $item = {
         *    "id_product": "11",
         *    "id_product_attribute": "27",
         *    "id_shop": "1",
         *    "id_currency": "1"
         *  }
        */
        foreach ($comboProducts as $item) {
            $hydratedProduct['parent_id'] = (int)$item['id_product'];
            $hydratedProduct['id'] = (int)$item['id_product_attribute'];
            $productObj = new Product($hydratedProduct['parent_id'], false, null, $id_shop);
            $imageUrls = CombinationPayloadService::buildComboImageUrls($productObj, $hydratedProduct['id'], $item['id_shop']);
            $cache_key = $item['id_product'] . '_' . $item['id_product_attribute'] . $item['id_shop'] . '_quantity';

            if (!Cache::isStored($cache_key)) {
                Cache::store(
                    $cache_key,
                    StockAvailable::getQuantityAvailableByProduct($item['id_product'], $item['id_product_attribute'], $item['id_shop'])
                );
            }
            $hydratedProduct['quantity'] = Cache::retrieve($cache_key);
            // Will need to format on receiving end
            // See Product::__construct() and \KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService::buildPayload

            // without tax, include discounts
            $hydratedProduct['price'] = ProductPayloadService::getPriceStatic(
                $item['id_shop'],
                $hydratedProduct['parent_id'],
                $shopDefaultCurrency->id,
                false,
                true,
                $hydratedProduct['id']
            );
            // with tax, include discounts
            $hydratedProduct['tax_incl_price'] = ProductPayloadService::getPriceStatic(
                $item['id_shop'],
                $hydratedProduct['parent_id'],
                $shopDefaultCurrency->id,
                true,
                true,
                $hydratedProduct['id']
            );
            // with tax, exclude discounts
            $hydratedProduct['discount_excl_price'] = ProductPayloadService::getPriceStatic(
                $item['id_shop'],
                $hydratedProduct['parent_id'],
                $shopDefaultCurrency->id,
                true,
                false,
                $hydratedProduct['id']
            );
            $hydratedProduct['currency'] = $shopDefaultCurrency->iso_code;
            $hydratedProduct['url'] = CombinationPayloadService::getComboUrl($hydratedProduct['parent_id'], $hydratedProduct['id'], $item['id_shop']);
            $hydratedProduct['image_full_url'] = $imageUrls;
            $hydratedProduct['name'] = CombinationPayloadService::getCombinationName($productObj, (int)$item['id_product_attribute']);
            array_push($hydratedComboProducts, $hydratedProduct);
        }

        return $hydratedComboProducts;
    }

    public function handleNextParam($nextParam)
    {
        // Return null if $nextParam is empty string or null (wasn't included in query params).
        if (!$nextParam) {
            return null;
        }
        try {
            $multiValues = explode(',', base64_decode($nextParam));
            // ["key1:val1", "key2:val2"] => ["key1"=>"val1", "key2"=>"val2"]
            foreach ($multiValues as $nextKey) {
                [$k, $v] = explode(':', $nextKey);
                $nextValue[$k] = $v;
            }
        } catch (Exception $e) {
            throw new WebserviceException(
                $e->getMessage(),
                [$this->DEFAULT_ERROR_CODE, 400]
            );
        }

        return $nextValue;
    }

    public function getCursorValue(&$records, $batchSize): string
    {
        $nextCursor = '';
        if (!$records) {
            return $nextCursor;
        }
        $cursorKeys = ['id_product', 'id_product_attribute'];
        if (!(count($records) < $batchSize)) {
            $cursorRecord = array_pop($records);
            $recordKeys = [];
            foreach ($cursorKeys as $k) {
                $recordKeys[] = $k . ':' . $cursorRecord[$k];
            }
            $nextCursor = base64_encode(implode(',', $recordKeys));
        }

        return $nextCursor;
    }
}
