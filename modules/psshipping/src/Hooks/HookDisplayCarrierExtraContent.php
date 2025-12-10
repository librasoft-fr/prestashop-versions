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
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierRepository;
use PrestaShop\Module\Psshipping\Domain\GelProximity\Models\PickupPoint;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class HookDisplayCarrierExtraContent
{
    /** @var Psshipping */
    private $psshipping;
    /** @var CarrierRepository */
    private $carrierRepository;
    /** @var Context */
    private $context;

    public function __construct(
        Psshipping $psshipping,
        CarrierRepository $carrierRepository,
        Context $context
    ) {
        $this->psshipping = $psshipping;
        $this->carrierRepository = $carrierRepository;
        $this->context = $context;
    }

    /**
     * @param array{
     *   carrier: array{
     *     id: int,
     *   }
     * } $params
     */
    public function run(array $params): string
    {
        if ($this->context->smarty === null) {
            throw new PsshippingException("The 'smarty' field is null in the context.");
        }

        $currentCarrierId = $params['carrier']['id'];

        if (!$this->carrierRepository->isPickupCarrierFromMapping($currentCarrierId)) {
            return '';
        }

        $pickupPointDetail = null;

        try {
            $pickupPoint = PickupPoint::fromCookies($this->context);
            $template = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'psshipping/views/templates/hook/pickUpPointDetails.tpl');
            $template->assign([
                'detailsTitle' => $this->psshipping->getTranslator()->trans('Pick-up point selected:', [], 'Modules.Psshipping.Admin'),
                'btnText' => $this->psshipping->getTranslator()->trans('Change pick-up point', [], 'Modules.Psshipping.Admin'),
                'pickupPoint' => $pickupPoint->toArray(),
            ]);
            $pickupPointDetail = $template->fetch();
        } catch (PsshippingException $th) {
        }

        $template = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'psshipping/views/templates/hook/gelProximityCarrierExtraContent.tpl');
        $template->assign([
            'pickupSelectionError' => $this->psshipping->getTranslator()->trans('An error occurred while selecting the pickup location, please try again.', [], 'Modules.Psshipping.Admin'),
            'pickupPointDetail' => $pickupPointDetail,
        ]);

        return $template->fetch();
    }
}
