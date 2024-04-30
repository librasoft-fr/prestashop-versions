<?php
/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

namespace PrestaShop\Module\Mbeshipping\Helper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MOrderHelper
{
    protected $_morder_table_name = 'mbe_shipping_order';

    public function installMOrderTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . bqSQL($this->_morder_table_name) . "`(
                `id_mbeshipping_order` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_order` int(10) default 0 not null,
                `is_download_available` int(10) default 0 not null,
                `is_pickup_mode` tinyint(1) default 0 not null,
                `id_mbeshipping_pickup_batch` int(10) default 0 not null,
                UNIQUE KEY MBE_ORDER_MO_UNIQUE (id_order))";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function uninstallMOrderTable()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($this->_morder_table_name) . "`";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function getDownloadAvailableByOrderId($id_order)
    {
        $main_table = _DB_PREFIX_ . bqSQL($this->_morder_table_name);

        $sql = "
        SELECT `is_download_available`
        FROM " . $main_table . "
        WHERE `id_order` = ".(int)$id_order;

        return \Db::getInstance()->getValue($sql);
    }

    public function insertOrder($id_order, $is_pickup_mode = 0)
    {
        if (!isset($id_order)) {
            return false;
        }

        try {
            return \Db::getInstance()->insert(
                bqSQL($this->_morder_table_name),
                [
                    'id_order' => (int)$id_order,
                    'is_pickup_mode' => (int)$is_pickup_mode,
                ]
            );
        } catch (\PrestaShopDatabaseException $e) {
            return false;
        }
    }

    public function setOrderDownloadAvailable($id_order, $is_download_available = 0)
    {
        if (!isset($id_order)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL($this->_morder_table_name),
            [
                'is_download_available' => (int)$is_download_available
            ],
            'id_order = ' . (int)$id_order
        );
    }

    public function setOrderPickupMode($id_order, $is_pickup_mode = 0)
    {
        if (!isset($id_order)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL($this->_morder_table_name),
            [
                'is_pickup_mode' => (int)$is_pickup_mode
            ],
            'id_order = ' . (int)$id_order
        );
    }

    public function setOrderPickupBatch($id_order, $id_mbeshipping_pickup_batch = null)
    {
        if (!isset($id_order)) {
            return false;
        }

        if (!isset($id_mbeshipping_pickup_batch)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL($this->_morder_table_name),
            [
                'id_mbeshipping_pickup_batch' => (int)$id_mbeshipping_pickup_batch
            ],
            'id_order = ' . (int)$id_order
        );
    }

    public function orderExists($id_order)
    {
        $query = new \DbQuery();
        $query->select('id_order');
        $query->from(bqSQL($this->_morder_table_name));
        $query->where('id_order = ' . (int)$id_order);

        return \Db::getInstance()->getValue($query);
    }

    public static function hasPickupBatchByOrderId($id_order)
    {
        $query = new \DbQuery();
        $query->select('id_mbeshipping_pickup_batch');
        $query->from(bqSQL('mbe_shipping_order'));
        $query->where('id_order = ' . (int)$id_order);

        $result = \Db::getInstance()->getValue($query);
        return $result && $result > 0;
    }

    public static function detachOrderIdFromPickupBatch($id_order)
    {
        if (!isset($id_order)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL('mbe_shipping_order'),
            [
                'id_mbeshipping_pickup_batch' => 0,
                'is_pickup_mode' => 0
            ],
            'id_order = ' . (int)$id_order
        );
    }

    public static function getShippingNumber($id_order)
    {
        $idOrderCarrier = (int)\Db::getInstance()->getValue('
                SELECT `id_order_carrier`
                FROM `' . _DB_PREFIX_ . 'order_carrier`
                WHERE `id_order` = ' . (int) $id_order);
        if (!$idOrderCarrier) {
            return null;
        }

        $orderCarrier = new \OrderCarrier($idOrderCarrier);

        return $orderCarrier->tracking_number;
    }
}
