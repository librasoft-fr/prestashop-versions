<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierRepository;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierService;

/**
 * @param Psshipping $module
 *
 * @return bool
 */
function upgrade_module_3_0_0($module)
{
    /** @var CarrierRepository $carrierRepository */
    $carrierRepository = new CarrierRepository($module);
    /** @var CarrierService $carrierService */
    $carrierService = new CarrierService($module, $carrierRepository);

    $newMapping = [];
    $mapping = $carrierRepository->getShippingCarriersMapping();

    foreach ($mapping as $idCarrier => $type) {
        $newMapping[$idCarrier] = [
            'type' => $type,
            'provider' => CarrierService::PROVIDER_MBE,
        ];
    }

    //@phpstan-ignore-next-line
    $carrierRepository->updateShippingCarrierMapping($newMapping);

    require_once __DIR__ . '/../src/Domain/Carriers/CarrierConfigurationInterface.php';
    require_once __DIR__ . '/../src/Domain/Carriers/CarrierConfiguration.php';

    $result = Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'carrier` SET `name`= "MBE Standard delivery" WHERE `name` = "Standard delivery"') &&
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'carrier` SET `name`= "MBE Express delivery" WHERE `name` = "Express delivery"') &&
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'carrier` SET `name`= "MBE Pick-up point" WHERE `name` = "Delivery point"');

    $previousQueryError = Db::getInstance()->getMsgError();
    $previousQueryErrorCode = Db::getInstance()->getNumberError();

    if (!empty($previousQueryError) || !empty($previousQueryErrorCode)) {
        throw new Exception('An error occured while upgrading the module');
    }

    $carriersId = Db::getInstance()->executeS('SELECT id_carrier FROM `' . _DB_PREFIX_ . 'carrier` WHERE name LIKE "MBE%" AND deleted = 0 AND external_module_name = "psshipping"');

    if (!empty($carriersId)) {
        if (is_array($carriersId)) {
            foreach ($carriersId as $carrierId) {
                $carrierService->setLogoToCarrier($carrierId['id_carrier'], 'mbe');
            }
        }
    }

    return $result;
}
