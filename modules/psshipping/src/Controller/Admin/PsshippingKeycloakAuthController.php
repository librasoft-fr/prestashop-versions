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

use PrestaShop\Module\Psshipping\Exception\BadRequestException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Service\Routing\Router;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PsshippingKeycloakAuthController extends FrameworkBundleAdminController
{
    public function oauthCallback(): void
    {
        /** @var Router $router */
        $router = $this->get('router');

        /** @var bool $callbackParam */
        $callbackParam = (bool) Tools::getValue('keycloak-onboarding-success', false);

        /** @var string $cbFromPage */
        $cbFromPage = Tools::getValue('from-page', '');
        $redirectUri = '';

        if ($callbackParam === false) {
            throw new BadRequestException('An error occured with keycloak oauth, missing callback parameter', 500);
        }

        $redirectUri = $router->generate('home') . '#/' . strval($cbFromPage);

        Tools::redirectAdmin($redirectUri);
    }
}
