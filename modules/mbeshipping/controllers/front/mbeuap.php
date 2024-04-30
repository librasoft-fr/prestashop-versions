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

use PrestaShop\Module\Mbeshipping\Helper\MdpHelper;
use PrestaShop\Module\Mbeshipping\Helper\DataHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MbeshippingMbeuapModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        try {
            $mbeSelectedUap = \Tools::getValue('mbeSelectedUap');

            $helper = new MdpHelper();
            $dataHelper = new DataHelper();

            if ($mbeSelectedUap !== -1) {
                $mbeUapList = \Tools::getValue('mbeUapList');

                $mbeUapList = $dataHelper->doUnserialize(base64_decode($mbeUapList));

                $uap = array_search($mbeSelectedUap, array_column($mbeUapList, 'PublicAccessPointID'));

                $selUap = array();
                $selUap['PublicAccessPointID'] = $mbeUapList[$uap]['PublicAccessPointID'];
                $selUap['ConsigneeName'] = $mbeUapList[$uap]['ConsigneeName'];
                $selUap['AddressLine'] = $mbeUapList[$uap]['AddressLine'];
                $selUap['PoliticalDivision2'] = $mbeUapList[$uap]['PoliticalDivision2'];
                $selUap['PostcodePrimaryLow'] = $mbeUapList[$uap]['PostcodePrimaryLow'];

                $cart = \Context::getContext()->cart;
                $mdpToInsert = base64_encode($dataHelper->doSerialize($selUap));
                $helper->insertMdp($mdpToInsert, $cart->id);
            } else {

            }


            die;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
