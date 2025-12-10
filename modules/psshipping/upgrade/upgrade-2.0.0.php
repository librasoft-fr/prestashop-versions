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
use PrestaShop\Module\Psshipping\Domain\Carriers\PickupCarrierConfiguration;
use PrestaShop\Module\Psshipping\Service\DatabaseInstaller;

/**
 * @param Psshipping $module
 *
 * @return bool
 */
function upgrade_module_2_0_0($module)
{
    $hookList = [
        'actionValidateOrder',
        'displayHeader',
        'displayCarrierExtraContent',
        'displayOrderConfirmation',
        'actionGetOrderShipments',
    ];

    $module->registerHook($hookList);

    /** @var CarrierRepository $carrierRepository */
    $carrierRepository = new CarrierRepository($module);
    /** @var CarrierService $carrierService */
    $carrierService = new CarrierService($module, $carrierRepository);
    $shippingCarriers = Db::getInstance()->executeS('SELECT id_carrier, deleted, external_module_name FROM `' . _DB_PREFIX_ . 'carrier` WHERE `external_module_name` IN ("psshipping_standard", "psshipping_express")');

    if (!empty($shippingCarriers)) {
        if (is_array($shippingCarriers)) {
            foreach ($shippingCarriers as $shippingCarrier) {
                if (filter_var($shippingCarrier['deleted'], FILTER_VALIDATE_BOOLEAN) === true) {
                    continue;
                }
                if ($shippingCarrier['external_module_name'] === 'psshipping_standard') {
                    $carrierRepository->addShippingCarrierMapping((int) $shippingCarrier['id_carrier'], CarrierService::CARRIERS_STANDARD);
                }
                if ($shippingCarrier['external_module_name'] === 'psshipping_express') {
                    $carrierRepository->addShippingCarrierMapping((int) $shippingCarrier['id_carrier'], CarrierService::CARRIERS_EXPRESS);
                }
            }
        }
    }

    // require_once is necessary because the previous autoloader is loaded in memory
    require_once __DIR__ . '/../src/Domain/Carriers/CarrierConfigurationInterface.php';
    require_once __DIR__ . '/../src/Domain/Carriers/CarrierConfiguration.php';
    require_once __DIR__ . '/../src/Domain/Carriers/PickupCarrierConfiguration.php';
    require_once __DIR__ . '/../src/Service/DatabaseInstaller.php';

    $result = Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'carrier` SET `external_module_name`= "psshipping", `is_module` = 1, `need_range` = 1 WHERE `external_module_name` IN ("psshipping_standard", "psshipping_express")')
        && (new DatabaseInstaller($module))->install();

    $mbe_tracking_url = $module->getMbeTrackingUrl();

    $carrierService->create(new PickupCarrierConfiguration($mbe_tracking_url))->jsonSerialize();

    return $result;
}
