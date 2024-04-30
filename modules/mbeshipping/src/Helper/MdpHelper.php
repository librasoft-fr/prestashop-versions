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

class MdpHelper
{
    protected $_mdp_table_name  = 'mbe_shipping_mdp';

    public function installMdpTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . bqSQL($this->_mdp_table_name) . "`(
                `id_mbeshippingmdp` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `mdp` LONGTEXT default '' not null,
                `id_cart` int default 0 not null,
                UNIQUE KEY MBE_CART_MDP_UNIQUE (id_cart))";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function uninstallMdpTable()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($this->_mdp_table_name) . "`";
        $result = \Db::getInstance()->execute($sql);
        return \Db::getInstance()->execute($sql);
    }

    public function insertMdp($mdp, $id_cart)
    {
        $sql = "
                INSERT INTO `" . _DB_PREFIX_ . bqSQL($this->_mdp_table_name) . "` (
                    `mdp`,`id_cart`
                ) 
                VALUES (
                    '" . pSQL($mdp) . "',
                    " . (int)$id_cart . ") ON DUPLICATE KEY UPDATE `mdp` = '" . pSQL($mdp) . "';";


        $insertResult = \Db::getInstance()->execute($sql);
        return $insertResult;
    }


	public function getMdpByCartId( $id_cart ) {
        $sql = "SELECT mdp FROM `" . _DB_PREFIX_ . bqSQL($this->_mdp_table_name) . "` WHERE id_cart = ".  (int)$id_cart;
		return \Db::getInstance()->executeS($sql);
	}

    public function deleteMdpByCartId( $id_cart ) {
        $sql = "DELETE FROM `" . _DB_PREFIX_ . bqSQL($this->_mdp_table_name) . "` WHERE id_cart = ".  (int)$id_cart;
        return \Db::getInstance()->executeS($sql);
    }
}
