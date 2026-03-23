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
use PrestaShop\Module\Psshipping\Domain\Carriers\Exception\CannotAddLogoToCarrierException;
use PrestaShop\Module\Psshipping\Domain\Carriers\Exception\UnableToFindCarrierException;
use PrestaShop\Module\Psshipping\Domain\Legacy\PrestaShopAdapter;
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

    /** @var Psshipping */
    private $module;

    const CARRIERS_STANDARD = 'standard';
    const CARRIERS_EXPRESS = 'express';
    const CARRIERS_PICKUP = 'pickup';
    const PROVIDER_MBE = 'mbe';
    const PROVIDER_UPS = 'ups';

    public function __construct(Psshipping $module, CarrierRepository $carrierRepository)
    {
        /** @var CommandBusInterface $commandBus */
        $commandBus = $module->getService('prestashop.core.command_bus');
        $this->module = $module;
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
            return CarrierDto::toDomain($carrier, $carrierDto->getType(), $carrierDto->getProvider());
        }

        $context = \Context::getContext();
        $carrier->save();

        if (!empty($carrier->id)) {
            $this->setLogoToCarrier(intval($carrier->id), $carrierDto->getProvider());
        }
        $carrier->setTaxRulesGroup((int) Configuration::get('PS_TAX'), false);

        $this->carrierRepository->addShippingCarrierMapping((int) $carrier->id, $carrierDto->getType(), $carrierDto->getProvider());

        if (!empty($context->language) && !empty($context->language->id)) {
            $carrier->setGroups(array_column(Group::getGroups($context->language->id), 'id_group'));
        }

        return CarrierDto::toDomain($carrier, $carrierDto->getType(), $carrierDto->getProvider());
    }

    public function update(): void
    {
        foreach ($this->get() as $carrierDetails) {
            foreach ($carrierDetails as $detail) {
                if (!empty($detail['id_carrier'])) {
                    $carrierCore = new Carrier((int) $detail['id_carrier']);
                    $carrierCore->deleted = false;
                    $carrierCore->update();
                }
            }
        }
    }

    public function delete(): void
    {
        foreach ($this->get() as $carrierDetails) {
            foreach ($carrierDetails as $detail) {
                if (!empty($detail['id_carrier'])) {
                    $carrierCore = new Carrier((int) $detail['id_carrier']);
                    $carrierCore->deleted = true;
                    $carrierCore->update();
                }
            }
        }
    }

    /**
     * @return array{
     *     standard: array{mbe: Carrier|null, ups: Carrier|null},
     *      express: array{mbe: Carrier|null, ups: Carrier|null},
     *       pickup: array{mbe: Carrier|null, ups: Carrier|null}
     *   }
     */
    public function get()
    {
        $carrierFromModule = [
            'standard' => [
                'mbe' => null,
                'ups' => null,
            ],
            'express' => [
                'mbe' => null,
                'ups' => null,
            ],
            'pickup' => [
                'mbe' => null,
                'ups' => null,
            ],
        ];

        $context = Context::getContext();

        if (!empty($context->link)) {
            $findCarriers = $this->carrierRepository->getCarriers();
            $mapping = $this->carrierRepository->getShippingCarriersMapping();

            foreach ($findCarriers as $carrier) {
                $carrier['id_carrier'] = (int) $carrier['id_carrier'];
                $carrier['detailLink'] = (new PrestaShopAdapter($this->module))->generateEditCarrierLink((int) $carrier['id_carrier']);

                foreach ($mapping as $idCarrier => $carrierType) {
                    if ((int) $idCarrier === $carrier['id_carrier']) {
                        $type = $carrierType;
                        $provider = null;
                        // need this condition to avoid error when upgrading because the carrier mapping format changed
                        /* @phpstan-ignore-next-line */
                        if (is_array($carrierType)) {
                            $type = $carrierType['type'];
                            $provider = $carrierType['provider'];
                        }

                        if (in_array($type, ['standard', 'express', 'pickup']) && in_array($provider, ['mbe', 'ups'])) {
                            $carrierFromModule[$type][$provider] = $carrier;
                        }
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
        $currentCarrierValue = $allShippingCarriers[$carrier->getType()][$carrier->getProvider()];

        if ($currentCarrierValue !== null && in_array($currentCarrierValue['id_carrier'], array_keys($mapping))) {
            if (filter_var($currentCarrierValue['deleted'], FILTER_VALIDATE_BOOLEAN) === true) {
                $this->setLogoToCarrier($currentCarrierValue['id_carrier'], $carrier->getProvider());
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

    /**
     * @param int $carrierId
     * @param string $provider
     */
    public function setLogoToCarrier(int $carrierId, string $provider): void
    {
        $imageSource = _PS_MODULE_DIR_ . 'psshipping/views/img/' . $provider . '.png';
        $imageDest = _PS_SHIP_IMG_DIR_ . $carrierId . '.jpg';
        if (!copy($imageSource, $imageDest)) {
            throw new CannotAddLogoToCarrierException('An error occured while copying img');
        }
    }
}
