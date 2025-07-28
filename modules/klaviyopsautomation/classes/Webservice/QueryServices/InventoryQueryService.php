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

use Db;
use DbQuery;

class InventoryQueryService
{
    use QueryServiceTrait;

    public static function getInventoryPage($shop, $limit, $cursor = null)
    {
        $query = new DbQuery();
        $query->select('s.id_stock_available, s.id_product, s.id_product_attribute, s.quantity, s.out_of_stock, p.active');
        $query->from('stock_available', 's');
        $query->innerJoin('product_shop', 'p', 's.id_product = p.id_product AND p.id_shop = ' . (int) $shop);
        $query->where('s.id_shop = ' . (int) $shop);
        if ($cursor) {
            $query->where('id_stock_available <= ' . (int) $cursor[1]);
        }
        $query->orderBy('id_stock_available DESC');
        $query->limit($limit);

        return Db::getInstance()->executeS($query->build());
    }

    public static function getInventoryPolicy($shop = null)
    {
        $query = new DbQuery();
        $query->select('value, id_shop');
        $query->from('configuration');
        $query->where('name = "' . "PS_ORDER_OUT_OF_STOCK" . '"');
        if (!is_null($shop)) {
            $query->where('id_shop = ' . (int) $shop);
        } else {
            $query->where('id_shop IS NULL');
        }
        $query->limit(1);


        return Db::getInstance()->executeS($query->build());
    }
}
