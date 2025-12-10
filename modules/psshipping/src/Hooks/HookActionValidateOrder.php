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

namespace PrestaShop\Module\Psshipping\Hooks;

use Cart;
use Context;
use Order;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierRepository;
use PrestaShop\Module\Psshipping\Domain\Carriers\PickupPoints\PsshippingAddressOrdersRepository;
use PrestaShop\Module\Psshipping\Domain\Carriers\PickupPoints\PsshippingAddressRepository;
use PrestaShop\Module\Psshipping\Domain\GelProximity\Models\PickupPoint;
use PrestaShop\Module\Psshipping\Entity\PsshippingAddress;
use PrestaShop\Module\Psshipping\Entity\PsshippingAddressOrders;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;

if (!defined('_PS_VERSION_')) {
    exit();
}

class HookActionValidateOrder
{
    /** @var CarrierRepository */
    private $carrierRepository;

    /** @var PsshippingAddressRepository */
    private $psshippingAddressRepository;

    /** @var PsshippingAddressOrdersRepository */
    private $psshippingAddressOrdersRepository;

    /** @var Context */
    private $context;

    public function __construct(
        CarrierRepository $carrierRepository,
        PsshippingAddressRepository $psshippingAddressRepository,
        PsshippingAddressOrdersRepository $psshippingAddressOrdersRepository,
        Context $context
    ) {
        $this->carrierRepository = $carrierRepository;
        $this->psshippingAddressRepository = $psshippingAddressRepository;
        $this->psshippingAddressOrdersRepository = $psshippingAddressOrdersRepository;
        $this->context = $context;
    }

    /**
     * @param array{
     *   cart: Cart,
     *   order: Order,
     * } $params
     */
    public function run(array $params): void
    {
        if ($params['order']->id_carrier === null) {
            throw new PsshippingException("The 'order->id_carrier' field is null.");
        }

        $currentCarrierId = (int) $params['order']->id_carrier;
        if (!$this->carrierRepository->isPickupCarrierFromMapping($currentCarrierId)) {
            return;
        }

        if ($params['order']->id === null) {
            throw new PsshippingException("The 'order->id' field is null.");
        }

        if ($this->context->shop === null) {
            throw new PsshippingException("The 'shop' field is not null in the context.");
        }

        if ($this->context->shop->id === null) {
            throw new PsshippingException("The 'shop->id' field is not null in the context.");
        }

        $id_order = (int) $params['order']->id;
        $shopId = $this->context->shop->id;

        $shippingAddress = $this->getOrCreatePsshippingAddress();
        $shippingAddressOrder = new PsshippingAddressOrders();
        $shippingAddressOrder->setIdOrder($id_order);
        $shippingAddressOrder->setIdShop($shopId);
        $shippingAddressOrder->setAddress($shippingAddress);

        $this->psshippingAddressOrdersRepository->add($shippingAddressOrder);
    }

    private function getOrCreatePsshippingAddress(): PsshippingAddress
    {
        $pickupPoint = PickupPoint::fromCookies($this->context);

        $shippingAddress = $this->psshippingAddressRepository->findOneByPickupPointId($pickupPoint->getPickupPointId());

        if ($shippingAddress === null) {
            $shippingAddress = new PsshippingAddress();
            $shippingAddress->setPickupPointId($pickupPoint->getPickupPointId());
            $shippingAddress->setPickupPointId($pickupPoint->getPickupPointId());
            $shippingAddress->setNetworkCode($pickupPoint->getNetworkCode());
            $shippingAddress->setCode($pickupPoint->getCode());
            $shippingAddress->setAddress($pickupPoint->getAddress());
            $shippingAddress->setDescription($pickupPoint->getDescription());
            $shippingAddress->setCity($pickupPoint->getCity());
            $shippingAddress->setZipCode($pickupPoint->getZipCode());
            $shippingAddress->setDepartment($pickupPoint->getDepartment());
            $shippingAddress->setCountry($pickupPoint->getCountry());

            $shippingAddress = $this->psshippingAddressRepository->add($shippingAddress);
        }

        return $shippingAddress;
    }
}
