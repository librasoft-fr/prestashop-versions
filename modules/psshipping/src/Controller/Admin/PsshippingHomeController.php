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

use Context;
use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsService;
use PrestaShop\Module\Psshipping\Domain\Billing\BillingService;
use Prestashop\ModuleLibMboInstaller\DependencyBuilder;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Service\Routing\Router;
use Psshipping;
use Shop as LegacyShop;
use ShopGroup as LegacyShopGroup;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PsshippingHomeController extends FrameworkBundleAdminController
{
    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    public function renderApp(): Response
    {
        $mboInstaller = new DependencyBuilder($this->module);
        $dependencies = $mboInstaller->handleDependencies();
        $isDependenciesMet = $mboInstaller->areDependenciesMet();

        if (false === $isDependenciesMet) {
            return $this->render(
                '@Modules/psshipping/views/templates/admin/dependency.html.twig',
                [
                    'dependencies' => $dependencies,
                ]
            );
        }

        $context = Context::getContext();
        /** @var Router $router */
        $router = $this->get('router');

        return $this->render(
            '@Modules/psshipping/views/templates/admin/index.html.twig',
            [
                'layoutTitle' => $this->module->displayName,
                'defaultIsoCode' => $context->language->iso_code ?? 'en',
                'psxShippingApiUrl' => $this->module->getApiUrl(),
                'contextPsAccounts' => (new AccountsService())->getAccountsContext($this->module),
                'tokenPsAccounts' => (new AccountsService())->getPsAccountToken($this->module),
                'psAccountCdnUrl' => (new AccountsService())->getPsAccountCdnUrl(),
                'psBillingContext' => (new BillingService())->getBillingContext($this->module),
                'isOnboardingDone' => (bool) $this->isOnboardingDone(),
                'shops' => $this->getShops(),
                'isMultiShopContext' => LegacyShop::isFeatureActive() && LegacyShop::getContext() !== LegacyShop::CONTEXT_SHOP,
                'routes' => $this->generateRouteLink($router),
                'phpVersion' => phpversion(),
                'psVersion' => _PS_VERSION_,
                'moduleKey' => $this->module->module_key,
                'moduleVersion' => $this->module->version,
                'activeHooks' => $this->getActiveHooks(),
            ]
        );
    }

    /**
     * Retrieve all shops
     *
     * @return array<array<string, string|int|bool>>
     */
    private function getShops()
    {
        $shops = LegacyShop::getShops();

        /** @var Router $router */
        $router = $this->get('router');

        $shops = array_map(function ($shop) use ($router) {
            return [
                'shopId' => intval($shop['id_shop']),
                'shopIdGroup' => intval($shop['id_shop_group']),
                'shopName' => (new LegacyShop($shop['id_shop']))->name,
                'shopGroupName' => (new LegacyShopGroup($shop['id_shop_group']))->name,
                'url' => $router->generate('home', ['setShopContext' => 's-' . $shop['id_shop']]),
                'active' => intval($shop['active']),
            ];
        }, $shops);

        return $shops;
    }

    /**
     * Retrieve if the user has done the onboarding or not
     *
     * @return bool
     */
    private function isOnboardingDone(): bool
    {
        $configuration = new Configuration();

        return (bool) $configuration->get('PS_SHIPPING_ONBOARDING_IS_DONE', false);
    }

    /**
     * @param Router $router
     *
     * @return array<string, string>
     */
    private function generateRouteLink(Router $router): array
    {
        return [
            'home' => $router->generate('home'),
            'createCarrier' => $router->generate('carrier_create'),
            'toggleCarrier' => $router->generate('carrier_toggle_status'),
            'getCarrier' => $router->generate('carrier_list'),
            'carrierDetailLink' => $router->generate('carrier_admin_link'),
            'toggleIsOnboardingDone' => $router->generate('onboarding_toggle_status'),
            'oauthKeycloakCallback' => $this->getBaseURI('callback_oauth_keycloak'),
            'getOrders' => $router->generate('list_orders'),
            'getTrackingNumberForManifest' => $router->generate('get_last_tracking_number'),
            'psAccountsDebugLink' => $this->getPsAccountsDebugLink(),
            'orderStatusLink' => $this->getOrderStatusLink(),
            'setDimensions' => $router->generate('save_dimensions_per_packages'),
            'getDimensions' => $router->generate('get_dimensions_per_packages'),
            'getAdvancedSetting' => $router->generate('get_advanced_setting'),
            'setAdvancedSetting' => $router->generate('save_advanced_setting'),
            'registerHooks' => $router->generate('register_hooks'),
            'ordersLink' => $router->generate('admin_orders_index'),
            'getOrdersStatus' => $router->generate('get_orders_status'),
            'setOrderStatusMapping' => $router->generate('set_order_status_mapping'),
            'setStatusForOrderStatusMapping' => $router->generate('set_status_for_order_status_mapping'),
            'getStatusOrderStatusMapping' => $router->generate('get_status_order_status_mapping'),
            'getItalyState' => $router->generate('get_italy_state'),
        ];
    }

    /**
     * @param string $route
     *
     * @return string
     */
    private function getBaseURI(string $route)
    {
        $context = \Context::getContext();

        if (!empty($context->link) && !empty($context->shop)) {
            $redirectUri = $context->link->getAdminLink('KeycloakAuthController', true, [
                'route' => $route,
            ]);

            if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
                return $redirectUri;
            }

            // in some strange cases that are still not identified, on some ps versions
            // link->getAdminLink() generate also the base URI so we don't need go further and
            // generate it again
            if (strpos($redirectUri, 'http') !== false) {
                return $redirectUri;
            }

            return rtrim((string) $context->shop->getBaseURL(true), '/') . $redirectUri;
        }

        return '';
    }

    private function getPsAccountsDebugLink(): string
    {
        $context = \Context::getContext();

        if (!empty($context->link) && !empty($context->shop)) {
            return $context->link->getAdminLink('AdminDebugPsAccounts');
        }

        return '';
    }

    private function getOrderStatusLink(): string
    {
        $context = \Context::getContext();

        if (!empty($context->link) && !empty($context->shop)) {
            return $context->link->getAdminLink('AdminStatuses');
        }

        return '';
    }

    /**
     * @return array<string, bool>
     */
    private function getActiveHooks()
    {
        $hooks = [];
        $context = \Context::getContext();

        if (!empty($context->link) && !empty($context->shop)) {
            foreach (Psshipping::HOOK_LIST as $hook) {
                $hooks[$hook] = \Hook::isModuleRegisteredOnHook($this->module, $hook, $context->shop->id);
            }
        }

        return $hooks;
    }

    public function activateHooks(): Response
    {
        /* @phpstan-ignore-next-line */
        $this->module->registerHook(Psshipping::HOOK_LIST);

        return new Response(
            json_encode([
                'success' => true,
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
