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

namespace PrestaShop\Module\Psshipping\Domain\Legacy;

use PrestaShopBundle\Service\Routing\Router;
use Psshipping;

class PrestaShopAdapter
{
    /**
     * @var Psshipping
     */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    /**
     * @param array<string, string> $sfParams
     * @param array<string, string> $params
     */
    public function generateRoute(string $routeName, array $sfParams = [], array $params = []): string
    {
        if (version_compare(_PS_VERSION_, '1.7.8.0', '>=')) {
            /** @var Router $router */
            $router = $this->module->get('router');

            return $router->generate($routeName, $params);
        }

        $context = \Context::getContext();

        if (!empty($context->link) && !empty($context->shop)) {
            return $context->link->getAdminLink($routeName, true, $sfParams, $params);
        }

        return '';
    }

    public function generateOrderLink(int $orderId): string
    {
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            /** @var Router $router */
            $router = $this->module->get('router');

            return $router->generate('admin_orders_view', ['orderId' => $orderId]);
        }

        $context = \Context::getContext();

        if (!empty($context->link) && !empty($context->shop)) {
            return $context->link->getAdminLink('AdminOrders', true, [], ['vieworder' => '', 'id_order' => $orderId]);
        }

        return '';
    }
}
