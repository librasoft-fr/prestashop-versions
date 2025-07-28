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

namespace PrestaShop\Module\Psshipping\Controller\Admin;

use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierService;
use PrestaShop\Module\Psshipping\Domain\Carriers\ExpressCarrierConfiguration;
use PrestaShop\Module\Psshipping\Domain\Carriers\StandardCarrierConfiguration;
use PrestaShop\Module\Psshipping\Exception\BadRequestException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psshipping;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PsshippingCarrierController extends FrameworkBundleAdminController
{
    /** @var CarrierService */
    private $carrierService;

    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    public function createAction(): Response
    {
        try {
            $this->carrierService = new CarrierService($this->getCommandBus(), $this->module);

            $mbe_tracking_url = $this->module->getMbeTrackingUrl();

            return new Response(
                json_encode([
                    'success' => true,
                    'carrier' => [
                        'standard' => $this->carrierService->create(new StandardCarrierConfiguration($mbe_tracking_url)),
                        'pickup' => [],
                        'express' => $this->carrierService->create(new ExpressCarrierConfiguration($mbe_tracking_url)),
                    ],
                ]),
                201,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }

    public function listAction(): Response
    {
        try {
            $this->carrierService = new CarrierService($this->getCommandBus(), $this->module);

            return new Response(
                json_encode(['carriers' => $this->carrierService->get()]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }

    public function toggleStatusAction(Request $request): Response
    {
        try {
            $this->carrierService = new CarrierService($this->getCommandBus(), $this->module);
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);

            if (empty($requestBodyContent['idCarrier'])) {
                return new Response(
                    json_encode(['error' => 'Missing key idCarrier']),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            if (!is_int($requestBodyContent['idCarrier'])) {
                return new Response(
                    json_encode(['error' => 'idCarrier need to be an integer']),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            return new Response(
                json_encode(['carrierStatus' => $this->carrierService->toggle($requestBodyContent['idCarrier'])]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }

    public function getAdminLinkAction(Request $request): Response
    {
        try {
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);

            if (empty($requestBodyContent['idCarrier'])) {
                return new Response(
                    json_encode(['error' => 'No carrier ID provided']),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            return new Response(
                json_encode(['carrierLink' => $this->getAdminLink('AdminCarrierWizard', ['id_carrier' => $requestBodyContent['idCarrier']], true)]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }
}
