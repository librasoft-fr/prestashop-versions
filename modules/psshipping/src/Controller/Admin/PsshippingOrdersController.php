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

use Context as LegacyContext;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierService;
use PrestaShop\Module\Psshipping\Domain\Legacy\PrestaShopAdapter;
use PrestaShop\Module\Psshipping\Domain\Orders\OrdersService;
use PrestaShop\Module\Psshipping\Exception\BadRequestException;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psshipping;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PsshippingOrdersController extends FrameworkBundleAdminController
{
    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    public function listOrdersAction(Request $request): Response
    {
        try {
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
            $carriers = (new CarrierService($this->getCommandBus(), $this->module))->get();
            $itemPerPage = 10;
            $pageNumber = 1;
            $prestashopAdapter = new PrestaShopAdapter($this->module);

            if (empty($requestBodyContent['pageNumber']) && $requestBodyContent['pageNumber'] < 0) {
                return new Response(
                    json_encode([
                        'status' => false,
                        'error' => 'parameter pageNumber is not valid',
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            if (empty($requestBodyContent['itemPerPage']) && $requestBodyContent['itemPerPage'] < 0) {
                return new Response(
                    json_encode([
                        'status' => false,
                        'error' => 'parameter itemPerPage is not valid',
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }
            if (is_int($requestBodyContent['pageNumber'])) {
                $pageNumber = intval($requestBodyContent['pageNumber']);
            }

            if (is_int($requestBodyContent['itemPerPage'])) {
                $itemPerPage = intval($requestBodyContent['itemPerPage']);
            }
            $offset = ($pageNumber - 1) * $itemPerPage;
            $orders = (new OrdersService($this->module))->getOrdersByCustomCarriers($carriers, $prestashopAdapter, $itemPerPage, $offset);
            $nbrOrders = intval($orders['ordersCount']);
            $totalPages = ceil($nbrOrders / $itemPerPage);

            return new Response(
                json_encode([
                    'orders' => $orders['orders'],
                    'totalOrders' => $nbrOrders,
                    'pageNumber' => $pageNumber,
                    'totalPages' => $totalPages,
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }

    public function saveTrackingNumberAction(Request $request): Response
    {
        try {
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
            $orderId = 0;
            $trackingNumber = '';
            if (empty($requestBodyContent['trackingNumber']) && $requestBodyContent['orderId']) {
                return new Response(
                    json_encode([
                        'status' => false,
                        'error' => 'Missing trackingNumber | orderId parameters',
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            if (is_int($requestBodyContent['orderId'])) {
                $orderId = intval($requestBodyContent['orderId']);
            }

            if (is_string($requestBodyContent['trackingNumber'])) {
                $trackingNumber = (string) $requestBodyContent['trackingNumber'];
            }

            return new Response(
                json_encode([
                    'status' => (new OrdersService($this->module))->saveTrackingNumber($orderId, $trackingNumber),
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }

    public function getTrackingNumberAction(): Response
    {
        try {
            $carriersId = [];
            foreach ((new CarrierService($this->getCommandBus(), $this->module))->get() as $carriers) {
                foreach ($carriers as $carrier) {
                    if (!empty($carrier['id_carrier'])) {
                        $carriersId[] = (int) $carrier['id_carrier'];
                    }
                }
            }

            $getTrackingNumber = (new OrdersService($this->module))->getLastTrackingNumber($carriersId);

            return new Response(
                json_encode([
                    'trackingNumber' => $getTrackingNumber,
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }

    public function getOrdersStatusAction(): Response
    {
        try {
            $configuration = new Configuration();
            $mapping = '';
            $context = LegacyContext::getContext();
            if (!empty($context) && !empty($context->shop)) {
                $configuration->restrictUpdatesTo($context->shop);
            }
            if (is_string($configuration->get('PS_SHIPPING_ORDER_STATUS_MAPPING', ''))) {
                $mapping = $configuration->get('PS_SHIPPING_ORDER_STATUS_MAPPING', '');
            }

            $orderStatus = (new OrdersService($this->module))->getOrdersStatus();

            return new Response(
                json_encode([
                    'status' => $orderStatus,
                    'mapping' => json_decode($mapping) ?? [],
                    'isActivate' => (bool) $configuration->get('PS_SHIPPING_ORDER_MAPPING_IS_ACTIVATE', false),
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }
}
