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
use Media;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierRepository;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierService;
use PrestaShop\Module\Psshipping\Domain\GelProximity\GelProximityService;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;
use Psshipping;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit();
}

class HookDisplayHeader
{
    private const ALLOWED_CONTROLLERS = ['order'];
    /** @var Psshipping */
    private $psshipping;
    /** @var CarrierRepository */
    private $carrierRepository;
    /** @var GelProximityService */
    private $gelProximityService;
    /** @var Context */
    private $context;
    /** @var string */
    private $gelEndUserUrl;

    public function __construct(
        Psshipping $psshipping,
        GelProximityService $gelProximityService,
        CarrierRepository $carrierRepository,
        Context $context,
        string $gelEndUserUrl
    ) {
        $this->psshipping = $psshipping;
        $this->gelProximityService = $gelProximityService;
        $this->carrierRepository = $carrierRepository;
        $this->gelEndUserUrl = $gelEndUserUrl;
        $this->context = $context;
    }

    public function run(): void
    {
        if ($this->context->controller === null) {
            throw new PsshippingException("The 'controller' field is not null in the context.");
        }

        if (empty($this->context->controller->php_self) || (isset($this->context->controller->ajax) && $this->context->controller->ajax === true)) {
            return;
        }

        $controller = Tools::getValue('controller', $this->context->controller->php_self);

        if (in_array($controller, self::ALLOWED_CONTROLLERS, true)) {
            if ($this->context->cart === null) {
                throw new PsshippingException("The 'cart' field is null in the context.");
            }

            if ($this->context->link === null) {
                throw new PsshippingException("The 'link' field is null in the context.");
            }

            if ($this->context->language === null) {
                throw new PsshippingException("The 'language' field is null in the context.");
            }

            if ($this->context->cart->id === null) {
                throw new PsshippingException("The 'cart->id' field is null in the context.");
            }

            $carrierId = array_search(CarrierService::CARRIERS_PICKUP, $this->carrierRepository->getShippingCarriersMapping());

            try {
                $gelConfig = $this->gelProximityService->getGelCredentials();
            } catch (\Throwable $th) {
                // if the module is configured, but there is an error while retrieving the gel IDs
                if ($carrierId) {
                    Media::addJsDef([
                        'pickupCarrierError' => $this->psshipping->getTranslator()->trans('Unable to use this carrier at the moment.', [], 'Modules.Psshipping.Admin'),
                    ]);
                }

                // if the module is not configured but installed
                return;
            }

            $moduleLink = $this->context->link->getModuleLink(Psshipping::MODULE_TECHNICAL_NAME, 'GelProximityNotificationHandler');

            Media::addJsDef([
                'gelProximityConfig' => [
                    'ajaxUrl' => $moduleLink,
                    'merchantCode' => $gelConfig->getMerchantCode(),
                    'apiKey' => $gelConfig->getApiKey(),
                    'urlEndUser' => $this->gelEndUserUrl,
                    'reference' => GelProximityService::buildSessionReference($this->context->cart->id),
                    'carrierId' => $carrierId,
                    'selectPickupCarrierBtnText' => $this->psshipping->getTranslator()->trans('Select pick-up point', [], 'Modules.Psshipping.Admin'),
                    'locale' => str_replace('-', '_', $this->context->language->locale),
                ],
            ]);
        }
    }
}
