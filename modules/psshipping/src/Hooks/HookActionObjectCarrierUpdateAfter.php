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

use Carrier;
use Context;
use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsService;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierRepository;
use PrestaShop\Module\Psshipping\Domain\Orders\OrdersRepository;
use PrestaShop\Module\Psshipping\Domain\Segment\Segment;
use Psshipping;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit();
}

class HookActionObjectCarrierUpdateAfter
{
    /** @var Psshipping */
    private $psshipping;
    /** @var CarrierRepository */
    private $carrierRepository;
    /** @var OrdersRepository */
    private $orderRepository;
    /** @var ?int */
    private $oldCarrierIdCache = null;

    public function __construct(
        Psshipping $psshipping,
        CarrierRepository $carrierRepository,
        OrdersRepository $orderRepository
    ) {
        $this->psshipping = $psshipping;
        $this->carrierRepository = $carrierRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param array<string, string> $params
     */
    public function run(array $params): void
    {
        /** @var Carrier $carrier */
        $carrier = $params['object'];

        if ($carrier->external_module_name !== Psshipping::MODULE_TECHNICAL_NAME) {
            return;
        }

        $this->updateShippingCarrierMapping($carrier);
        $this->listenerUpdateFromStatusButton();
        $this->listenerUpdateFromEditButton($carrier);
    }

    /**
     * This function is used for listening changes if the user enabled or disabled the status carrier
     * from on the grid carrier page.
     */
    private function listenerUpdateFromStatusButton(): void
    {
        if (!empty(Context::getContext()->controller->controller_name) && Context::getContext()->controller->controller_name !== 'AdminCarriers') {
            return;
        }

        $carrierId = is_numeric(Tools::getValue('id_carrier')) ? (int) Tools::getValue('id_carrier') : null;

        if (Tools::getValue('statuscarrier') === '' && $carrierId) {
            $this->trackCarrierActiveChanges($carrierId);

            return;
        }
    }

    /**
     * This function is used for listening changes if the user enabled or disabled the status carrier
     * from the carrier page itself.
     */
    private function listenerUpdateFromEditButton(Carrier $carrier): void
    {
        if (!empty(Context::getContext()->controller->controller_name) && Context::getContext()->controller->controller_name !== 'AdminCarrierWizard') {
            return;
        }

        $oldCarrierId = is_numeric(Tools::getValue('id_carrier')) ? (int) Tools::getValue('id_carrier') : null;

        // When an edit is made on a carrier, the old one is set to 'deleted' in the database.
        // The new one, we only want to track the new one witch is a duplicated from the other one.
        if ($oldCarrierId === $carrier->id) {
            return;
        }

        $oldCarrier = new Carrier($oldCarrierId);

        if ($oldCarrier->active !== $carrier->active && $carrier->id) {
            $this->trackCarrierActiveChanges($carrier->id);
        }
    }

    private function trackCarrierActiveChanges(int $carrierId): void
    {
        $carrier = new Carrier($carrierId);
        $eventAction = $carrier->active ? 'Enabled' : 'Disabled';
        $account = (new AccountsService())->getAccountsContext($this->psshipping);

        $segment = new Segment($this->psshipping);
        $segment->setMessage('[SHI] Carrier ' . $eventAction);
        $segment->setOptions([
            'date' => date('Ymd'),
            'enabled' => (bool) $carrier->active,
            'email' => !empty($account['user']['email']) ? $account['user']['email'] : null,
            'carrier_name' => $carrier->name,
            'mbe_shipping_service' => explode('_', $carrier->external_module_name)[1],
        ]);
        $segment->track();
    }

    private function updateShippingCarrierMapping(Carrier $carrier): void
    {
        $oldCarrierId = is_numeric(Tools::getValue('id_carrier')) ? (int) Tools::getValue('id_carrier') : null;

        if ($oldCarrierId === $carrier->id && filter_var($carrier->deleted, FILTER_VALIDATE_BOOLEAN) === true) {
            $this->oldCarrierIdCache = $oldCarrierId;

            return;
        }

        if ($this->oldCarrierIdCache) {
            $mapping = $this->carrierRepository->getShippingCarriersMapping();
            $type = $mapping[$this->oldCarrierIdCache];
            unset($mapping[$this->oldCarrierIdCache]);
            $mapping[$carrier->id] = $type;
            $this->carrierRepository->updateShippingCarrierMapping($mapping);
            $this->orderRepository->updateCarrierForOrders($this->oldCarrierIdCache, (int) $carrier->id);
            $this->oldCarrierIdCache = null;
        }
    }
}
