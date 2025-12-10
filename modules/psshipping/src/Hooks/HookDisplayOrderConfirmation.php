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

use Context;
use Order;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierRepository;
use PrestaShop\Module\Psshipping\Domain\Carriers\PickupPoints\PsshippingAddressOrdersRepository;
use PrestaShop\Module\Psshipping\Domain\GelProximity\Models\PickupPoint;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class HookDisplayOrderConfirmation
{
    /** @var Psshipping */
    private $psshipping;
    /** @var CarrierRepository */
    private $carrierRepository;
    /** @var PsshippingAddressOrdersRepository */
    private $psshippingAddressOrdersRepository;
    /** @var Context */
    private $context;

    public function __construct(
        Psshipping $psshipping,
        CarrierRepository $carrierRepository,
        PsshippingAddressOrdersRepository $psshippingAddressOrdersRepository,
        Context $context
    ) {
        $this->psshipping = $psshipping;
        $this->carrierRepository = $carrierRepository;
        $this->psshippingAddressOrdersRepository = $psshippingAddressOrdersRepository;
        $this->context = $context;
    }

    /**
     * @param array{
     *   order: Order,
     * } $params
     */
    public function run(array $params): string
    {
        $currentCarrierId = (int) $params['order']->id_carrier;

        if (!$this->carrierRepository->isPickupCarrierFromMapping($currentCarrierId)) {
            return '';
        }

        try {
            $pickupPoint = PickupPoint::fromCookies($this->context);

            PickupPoint::unsetFromCookies($this->context);
        } catch (PsshippingException $e) {
            $orderId = (int) $params['order']->id;
            $shopId = (int) $params['order']->id_shop;

            $shippingAddress = $this->psshippingAddressOrdersRepository->findOneByIdOrderAndShop($orderId, $shopId);

            if ($shippingAddress === null) {
                return '';
            }

            $pickupPoint = PickupPoint::fromShippingAddress($shippingAddress->getAddress());
        }

        if ($this->context->smarty === null) {
            throw new PsshippingException("The 'smarty' field is null in the context.");
        }

        $template = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'psshipping/views/templates/hook/pickUpPointDetailsOrderConfirmation.tpl');
        $template->assign([
           'detailsTitle' => $this->psshipping->getTranslator()->trans('Your order will be ready at the selected pick-up point:', [], 'Modules.Psshipping.Admin'),
           'pickupPoint' => $pickupPoint->toArray(),
       ]);

        return $template->fetch();
    }
}
