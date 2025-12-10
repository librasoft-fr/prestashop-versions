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

namespace PrestaShop\Module\Psshipping\Domain\Carriers;

use Carrier;
use Configuration;
use Context;
use Group;
use PrestaShop\Module\Psshipping\Domain\Carriers\Exception\UnableToFindCarrierException;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\Command\ToggleCarrierStatusCommand;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class CarrierService
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var CarrierRepository */
    private $carrierRepository;

    const CARRIERS_STANDARD = 'standard';
    const CARRIERS_EXPRESS = 'express';
    const CARRIERS_PICKUP = 'pickup';

    public function __construct(Psshipping $module, CarrierRepository $carrierRepository)
    {
        /** @var CommandBusInterface $commandBus */
        $commandBus = $module->getService('prestashop.core.command_bus');
        $this->commandBus = $commandBus;
        $this->carrierRepository = $carrierRepository;
    }

    /**
     * @param CarrierConfiguration $carrierConfiguration
     *
     * @return CarrierDto
     */
    public function create($carrierConfiguration)
    {
        $carrierDto = $carrierConfiguration->transform();
        $carrier = CarrierDto::fromDomain($carrierDto);

        if ($this->isCarrierExists($carrierDto)) {
            return CarrierDto::toDomain($carrier, $carrierDto->getType());
        }

        $context = \Context::getContext();
        $carrier = CarrierDto::fromDomain($carrierDto);
        $carrier->save();
        $carrier->setTaxRulesGroup((int) Configuration::get('PS_TAX'), false);

        $this->carrierRepository->addShippingCarrierMapping((int) $carrier->id, $carrierDto->getType());

        if (!empty($context->language) && !empty($context->language->id)) {
            $carrier->setGroups(array_column(Group::getGroups($context->language->id), 'id_group'));
        }

        return CarrierDto::toDomain($carrier, $carrierDto->getType());
    }

    public function update(): void
    {
        foreach ($this->get() as $carrierDetail) {
            if (!empty($carrierDetail['id_carrier'])) {
                $carrierCore = new Carrier((int) $carrierDetail['id_carrier']);
                $carrierCore->active = false;
                $carrierCore->update();
            }
        }
    }

    public function delete(): void
    {
        foreach ($this->get() as $carrierDetail) {
            if (!empty($carrierDetail['id_carrier'])) {
                $carrierCore = new Carrier((int) $carrierDetail['id_carrier']);
                $carrierCore->deleted = true;
                $carrierCore->update();
            }
        }
    }

    /**
     * @return array<string,?Carrier>
     */
    public function get()
    {
        $carrierFromModule = [
            self::CARRIERS_STANDARD => null,
            self::CARRIERS_EXPRESS => null,
            self::CARRIERS_PICKUP => null,
        ];
        $context = Context::getContext();

        if (!empty($context->link)) {
            $findCarriers = $this->carrierRepository->getCarriers();
            $mapping = $this->carrierRepository->getShippingCarriersMapping();

            foreach ($findCarriers as $carrier) {
                $carrier['id_carrier'] = (int) $carrier['id_carrier'];
                $carrier['detailLink'] = $context->link->getAdminLink('AdminCarrierWizard', true, [], ['id_carrier' => $carrier['id_carrier']]);

                foreach ($mapping as $idCarrier => $carrierType) {
                    if ((int) $idCarrier === $carrier['id_carrier']) {
                        $carrierFromModule[$carrierType] = $carrier;
                    }
                }
            }
        }

        return $carrierFromModule;
    }

    public function toggle(int $carrierId): bool
    {
        try {
            if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                $this->commandBus->handle(new ToggleCarrierStatusCommand($carrierId));

                return (bool) (new Carrier($carrierId))->active;
            } else {
                $carrier = new Carrier((int) $carrierId);
                $carrier->active = !$carrier->active;

                return true;
            }
        } catch (\Throwable $th) {
            throw new UnableToFindCarrierException($th->getMessage(), $th->getCode());
        }
    }

    /**
     * @param CarrierDto $carrier
     */
    private function isCarrierExists($carrier): bool
    {
        $allShippingCarriers = $this->get();
        $mapping = $this->carrierRepository->getShippingCarriersMapping();
        $currentCarrierValue = $allShippingCarriers[$carrier->getType()];

        if ($currentCarrierValue !== null && in_array($currentCarrierValue['id_carrier'], array_keys($mapping))) {
            if (filter_var($currentCarrierValue['deleted'], FILTER_VALIDATE_BOOLEAN) === true) {
                $this->enableCarrier($currentCarrierValue['id_carrier']);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $carrierId
     */
    private function enableCarrier($carrierId): void
    {
        $carrier = new Carrier((int) $carrierId);
        $carrier->deleted = false;
        $carrier->update();
    }
}
