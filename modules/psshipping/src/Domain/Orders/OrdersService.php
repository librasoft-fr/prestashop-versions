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

declare(strict_types=1);

namespace PrestaShop\Module\Psshipping\Domain\Orders;

use Carrier;
use Currency;
use PrestaShop\Module\Psshipping\Domain\Legacy\PrestaShopAdapter;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class OrdersService
{
    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    /**
     * @param array<int|string,array<Carrier>> $carriersFromShop
     * @param PrestaShopAdapter $prestashopAdapter
     * @param int $limit
     * @param int $offset
     *
     * @return array<string, array<int, array<string, int|string>>|int<0, max>>>>
     */
    public function getOrdersByCustomCarriers($carriersFromShop, $prestashopAdapter, $limit, $offset)
    {
        $carriersId = [];
        $orderRepository = new OrdersRepository($this->module);

        foreach ($carriersFromShop as $carriers) {
            foreach ($carriers as $carrier) {
                if (!empty($carrier['id_carrier'])) {
                    $carriersId[] = $carrier['id_carrier'];
                }
            }
        }
        $result = $orderRepository->getOrders($limit, $offset, $carriersId);
        $totalOrders = $orderRepository->getNbOrders($carriersId);

        foreach ($result as $key => $value) {
            $result[$key]['order_link'] = $prestashopAdapter->generateOrderLink((int) $value['id_order']);
        }

        return [
            'orders' => array_map([$this, 'convert'], $result),
            'ordersCount' => count($totalOrders),
        ];
    }

    /**
     * @param array<string, string|int|bool> $value
     *
     * @return array<string, int|string>
     */
    public function convert($value)
    {
        $orderDto = new OrdersDto();

        if (!empty($value['id_order'])) {
            $orderDto->setOrderId((int) $value['id_order']);
        }
        if (!empty($value['date_add'])) {
            $orderDto->setShippingDate(strval($value['date_add']));
        }
        if (!empty($value['name'])) {
            $orderDto->setOrderStatus(strval($value['name']));
        }
        if (!empty($value['reference'])) {
            $orderDto->setShippingId(strval($value['reference']));
        }
        if (!empty($value['total_shipping_tax_incl'])) {
            $orderDto->setShippingCost((int) round(intval($value['total_shipping_tax_incl']), 0));
        }
        if (!empty($value['order_link'])) {
            $orderDto->setOrderDetailLink(strval($value['order_link']));
        }
        if (!empty($value['id_currency'])) {
            $orderDto->setCurrency((new Currency(intval($value['id_currency'])))->iso_code);
        }
        if (!empty($value['color'])) {
            $orderDto->setOrderStatusBadgeColor(strval($value['color']));
        }

        if (!empty($value['external_module_name'])) {
            if ($value['external_module_name'] === 'psshipping_standard') {
                $orderDto->setShippingService('Standard');
            }
            if ($value['external_module_name'] === 'psshipping_express') {
                $orderDto->setShippingService('Express');
            }
            if ($value['external_module_name'] === 'psshipping_pickup') {
                $orderDto->setShippingService('Pickup');
            }
        }

        return $orderDto->toArray();
    }

    /**
     * @param int $idOrder
     * @param string $trackingNumber
     *
     * @return bool
     */
    public function saveTrackingNumber($idOrder, $trackingNumber)
    {
        return (new OrdersRepository($this->module))->saveTrackingNumber($idOrder, $trackingNumber);
    }

    /**
     * @param array<int<0, max>, int> $carriersId
     *
     * @return bool|int|string
     */
    public function getLastTrackingNumber($carriersId)
    {
        $trackingNumber = (new OrdersRepository($this->module))->getLastTrackingNumberByCarrierId($carriersId);

        return count($trackingNumber) > 0 ? $trackingNumber[0]['tracking_number'] : '';
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    public function getOrdersStatus()
    {
        return (new OrdersRepository($this->module))->getOrdersStatus();
    }
}
