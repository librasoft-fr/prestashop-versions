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

    /** @var Psshipping */
    private $module;

    const CARRIERS_CREATED_BY_MODULE = [
        'psshipping_standard',
        'psshipping_express',
        'psshipping_pickup',
    ];

    public function __construct(CommandBusInterface $commandBus, Psshipping $module)
    {
        $this->module = $module;
        $this->commandBus = $commandBus;
    }

    /**
     * @param CarrierConfiguration $carrierConfiguration
     *
     * @return CarrierDto
     */
    public function create($carrierConfiguration)
    {
        $carrierDto = $carrierConfiguration->transform();
        $totalCarriers = $this->get();
        $carrier = CarrierDto::fromDomain($carrierDto);

        if ($this->checkCarrierExists($totalCarriers[$carrier->external_module_name]) > 0) {
            return CarrierDto::toDomain($carrier);
        }

        $context = \Context::getContext();
        $carrier = CarrierDto::fromDomain($carrierDto);
        $carrier->save();
        $carrier->setTaxRulesGroup((int) Configuration::get('PS_TAX'), false);

        if (!empty($context->language) && !empty($context->language->id)) {
            $carrier->setGroups(array_column(Group::getGroups($context->language->id), 'id_group'));
        }

        return CarrierDto::toDomain($carrier);
    }

    public function update(): void
    {
        foreach ($this->get() as $carrier) {
            foreach ($carrier as $carrierDetail) {
                if (!empty($carrierDetail['id_carrier'])) {
                    $carrierCore = new Carrier((int) $carrierDetail['id_carrier']);
                    $carrierCore->active = false;
                    $carrierCore->update();
                }
            }
        }
    }

    public function delete(): void
    {
        foreach ($this->get() as $carrier) {
            foreach ($carrier as $carrierDetail) {
                if (!empty($carrierDetail['id_carrier'])) {
                    $carrierCore = new Carrier((int) $carrierDetail['id_carrier']);
                    $carrierCore->deleted = true;
                    $carrierCore->update();
                }
            }
        }
    }

    /**
     * @return array<int|string,array<Carrier>>
     */
    public function get()
    {
        $context = Context::getContext();

        if (!empty($context->link)) {
            $findCarriers = (new CarrierRepository($this->module))->getCarriers();

            $carrierFromModule = [
                'psshipping_standard' => [],
                'psshipping_express' => [],
                'psshipping_pickup' => [],
            ];

            foreach ($findCarriers as $carrier) {
                $carrier['id_carrier'] = (int) $carrier['id_carrier'];
                if (!empty($context->link)) {
                    $carrier['detailLink'] = $context->link->getAdminLink('AdminCarrierWizard', true, [], ['id_carrier' => $carrier['id_carrier']]);
                }
                $carrierFromModule[$carrier['external_module_name']][] = $carrier;
            }

            return $carrierFromModule;
        }

        return [];
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
     * @param Carrier[] $carriers
     */
    private function checkCarrierExists($carriers): int
    {
        $isCarrierActive = 0;

        foreach ($carriers as $carrier) {
            if (isset($carrier['deleted']) && (bool) $carrier['deleted'] === false) {
                ++$isCarrierActive;
            }
        }

        return $isCarrierActive;
    }
}
