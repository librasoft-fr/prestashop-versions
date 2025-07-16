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

if (!defined('_PS_VERSION_')) {
    exit();
}

use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsService;
use PrestaShop\Module\Psshipping\Domain\Api\Webhook;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierService;
use PrestaShop\Module\Psshipping\Domain\Legacy\PrestaShopAdapter;
use PrestaShop\Module\Psshipping\Domain\Segment\Segment;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;
use PrestaShop\Module\Psshipping\Helper\ConfigHelper;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Action\ActionsBarButtonsCollection as NewActionsBarButtonsCollection;
use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection;
use PrestaShopBundle\Service\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Twig\Environment as Twig_Environment;

class Psshipping extends Module
{
    /** @var bool */
    public $bootstrap;

    /** @var array<string, string> */
    public $ps_versions_compliancy;

    /** @var string */
    public $emailSupport;

    /** @var string */
    public $termsOfServiceUrl;

    /** @var string */
    public $name;

    /** @var string */
    public $version;

    /** @var string */
    public $module_key;

    /** @var ServiceContainer */
    public $serviceContainer;

    const HOOK_LIST = [
        'actionGetAdminOrderButtons',
        'displayAdminOrderTabContent',
        'displayAdminOrderTabLink',
        'displayAdminOrderContentShip',
        'displayAdminOrderTabShip',
        'displayBackOfficeHeader',
        'actionObjectCarrierUpdateAfter',
    ];

    /** @var array<array<string, string|bool>> */
    public $tabs = [
        [
            'name' => 'Homepage',
            'class_name' => 'PsshippingHomeController',
            'visible' => false,
            'parent_class_name' => 'AdminParentModulesSf',
            'wording' => 'Homepage',
            'wording_domain' => 'Modules.Pshipping.Admin',
        ],
    ];

    /**
     * Module constructor
     */
    public function __construct()
    {
        $this->name = 'psshipping';
        $this->tab = 'shipping_logistics';
        $this->version = '1.1.4';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->module_key = '8a4eff554bcb2ec847b3d4c70286b0e2';
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->emailSupport = 'support@prestashop.com';
        $this->termsOfServiceUrl =
            'https://www.prestashop.com/en/prestashop-account-privacy';
        $this->displayName = $this->trans('PrestaShop Shipping', [], 'Modules.Psshipping.Admin');
        $this->description = $this->trans(
            'Powered by Mail Boxes Etc., PrestaShop Shipping offers standard and express international delivery methods to your customers no matter where they are located. Have access to exclusive rates for each delivered and returned parcels by using our extensive network of trusted partners.',
            [],
            'Modules.Psshipping.Admin'
        );

        $this->confirmUninstall = $this->trans(
            'Are you sure you want to uninstall PrestaShop Shipping module?',
            [],
            'Modules.Psshipping.Admin'
        );

        require_once __DIR__ . '/vendor/autoload.php';

        if ($this->serviceContainer === null) {
            $this->serviceContainer = new ServiceContainer(
                (string) $this->name,
                $this->getLocalPath()
            );
        }
    }

    private function registerHooks(): bool
    {
        /* @phpstan-ignore-next-line */
        return (bool) $this->registerHook(self::HOOK_LIST);
    }

    public function install(): bool
    {
        $segment = new Segment($this, true);
        $segment->setMessage('[SHI] PS Shipping Installed');
        $segment->track();

        if (!$this->isPhpVersionCompliant()) {
            $this->_errors[] = $this->trans('This requires PHP 7.2 to work properly. Please upgrade your server configuration.', [], 'Modules.Psshipping.Admin');

            return defined('PS_INSTALLATION_IN_PROGRESS');
        }

        parent::install();

        return (bool) $this->registerHooks();
    }

    public function uninstall()
    {
        $segment = new Segment($this);
        $segment->setMessage('[SHI] PS Shipping Uninstalled');
        $segment->track();

        if (!$this->isPhpVersionCompliant()) {
            return parent::uninstall();
        }

        parent::uninstall();

        $configuration = new Configuration();
        $configuration->remove('PS_SHIPPING_ONBOARDING_IS_DONE');
        $configuration->remove('PS_SHIPPING_MAX_WEIGHT_PER_PACKAGE');
        $configuration->remove('PS_SHIPPING_MAX_WIDTH_PER_PACKAGE');
        $configuration->remove('PS_SHIPPING_MAX_HEIGHT_PER_PACKAGE');
        $configuration->remove('PS_SHIPPING_MAX_LENGTH_PER_PACKAGE');
        $configuration->remove('PS_SHIPPING_ORDER_MAPPING_IS_ACTIVATE');
        $configuration->remove('PS_SHIPPING_ORDER_STATUS_MAPPING');
        $configuration->remove('PS_SHIPPING_WEBHOOK_SECRET');
        (new Webhook($this))->deleteSvixEndpoint();

        /** @var PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface $commandBus */
        $commandBus = $this->getService('prestashop.core.command_bus');
        (new CarrierService($commandBus, $this))->delete();

        return true;
    }

    public function disable($force_all = false): bool
    {
        /** @var PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface $commandBus */
        $commandBus = $this->getService('prestashop.core.command_bus');

        (new CarrierService($commandBus, $this))->update();

        return parent::disable($force_all);
    }

    /**
     * Configuration page of the module - redirect to controller
     *
     * @return void
     */
    public function getContent(): void
    {
        $segment = new Segment($this);
        $segment->setMessage('[SHI] PS Shipping Module Manager Configure CTA Clicked');
        $segment->track();

        try {
            Tools::redirectAdmin((new PrestaShopAdapter($this))->generateRoute('home', ['route' => 'home']));
        } catch (\Exception $e) {
            if ($e instanceof RouteNotFoundException) {
                throw new PsshippingException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * Enable the new PrestaShop translations system for the module
     * https://devdocs.prestashop.com/1.7/modules/creation/module-translation/new-system/
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Method that dispatch to the correct service container to use
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        $splitServiceNamespace = explode('.', $serviceName);
        $firstLevelNamespace = $splitServiceNamespace[0];
        // if serviceName is not a service coming from the module
        if ($firstLevelNamespace !== 'psshipping') {
            // use symfony service container from prestashop
            return $this->get($serviceName);
        }

        // otherwise use the service container from the module
        return $this->serviceContainer->getService($serviceName);
    }

    /**
     * Hook executed on the header of each pages in the backoffice
     *
     * @return string|void
     */
    public function hookDisplayBackOfficeHeader()
    {
        return $this->renderPromoteBanner();
    }

    /**
     * Render the banner promoting the shipping module.
     * Only display the banner on the carrier page.
     * To be called on the backofficeHeader hook.
     *
     * @return string
     */
    private function renderPromoteBanner(): string
    {
        if (empty($this->context->controller)) {
            return '';
        }

        $controller = $this->context->controller;

        if (empty($controller->controller_name)) {
            return '';
        }

        $controllerName = $controller->controller_name;

        if ($controllerName !== 'AdminCarriers') {
            return '';
        }

        Media::addJsDef([
            'psshippingModuleLink' => (new PrestaShopAdapter($this))->generateRoute('home', ['route' => 'home']),
            'defaultIsoCode' => $this->context->language->iso_code ?? 'en',
        ]);

        $assets = $this->getAssetsPath();

        if (empty($this->context->smarty)) {
            return '';
        }

        $this->context->smarty->assign($assets);

        return $this->display(__DIR__, '/views/templates/hook/promoteBanner.tpl');
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return __FILE__;
    }

    /**
     * @return bool
     */
    public function useHotReload(): bool
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return (bool) $config->vue_app_dev_mode;
    }

    /**
     * @return string
     */
    public function getSegmentKey(): string
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return $config->segment_key;
    }

    /**
     * @return string
     */
    public function getSentryDsn(): string
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return $config->sentry_dsn;
    }

    /**
     * @return string
     */
    public function getSentryEnv(): string
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return $config->sentry_env;
    }

    /**
     * @return string
     */
    public function getCdnUrl(): string
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return $config->cdn_url;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return $config->api_url;
    }

    public function isDevEnvironment(): bool
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return (bool) $config->use_local_vue_app;
    }

    public function getMbeTrackingUrl(): string
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return $config->mbe_tracking_url;
    }

    /**
     * Add buttons to main buttons bar
     *
     * @param array<string, string> $params
     *
     * @return void
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        if (!$this->canDisplayShippingTab((int) $params['id_order'])) {
            return;
        }

        /** @var ActionsBarButtonsCollection|NewActionsBarButtonsCollection $bar */
        $bar = $params['actions_bar_buttons_collection'];

        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            $bar->add(
                new PrestaShop\PrestaShop\Core\Action\ActionsBarButton(
                    'btn-dark',
                    ['href' => '#psshipping-app'],
                    $this->trans('Print shipping label', [], 'Modules.Psshipping.Admin')
                )
            );
        } else {
            $bar->add(
                new PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-dark',
                    ['href' => '#psshipping-app'],
                    $this->trans('Print shipping label', [], 'Modules.Psshipping.Admin')
                )
            );
        }
    }

    /**
     * We use this hook in order to listen changes made on carriers created
     * by the PrestaShop Shipping module. We only want to listen 'active'
     * field edit
     *
     * @param array<string, string> $params
     */
    public function hookActionObjectCarrierUpdateAfter(array $params): void
    {
        /** @var Carrier $carrier */
        $carrier = $params['object'];

        if (!in_array($carrier->external_module_name, CarrierService::CARRIERS_CREATED_BY_MODULE)) {
            return;
        }

        $this->listenerUpdateFromStatusButton();
        $this->listenerUpdateFromEditButton($carrier);
    }

    /**
     * This function is used for listening changes if the user enabled or disabled the status carrier
     * from on the grid carrier page.
     */
    private function listenerUpdateFromStatusButton(): void
    {
        if (!empty($this->context->controller->controller_name) && $this->context->controller->controller_name !== 'AdminCarriers') {
            return;
        }

        $carrierId = is_numeric(\Tools::getValue('id_carrier')) ? (int) \Tools::getValue('id_carrier') : null;

        if (\Tools::getValue('statuscarrier') === '' && $carrierId) {
            $this->trackCarrierActiveChanges($carrierId);

            return;
        }
    }

    /**
     * This function is used for listening changes if the user enabled or disabled the status carrier
     * from the carrier page itself.
     */
    private function listenerUpdateFromEditButton(Carrier $carrier): void
    {
        if (!empty($this->context->controller->controller_name) && $this->context->controller->controller_name !== 'AdminCarrierWizard') {
            return;
        }

        $oldCarrierId = is_numeric(\Tools::getValue('id_carrier')) ? (int) \Tools::getValue('id_carrier') : null;

        // When an edit is made on a carrier, the old one is set to 'deleted' in the database.
        // The new one, we only want to track the new one witch is a duplicated from the other one.
        if ($oldCarrierId === $carrier->id) {
            return;
        }

        $oldCarrier = new \Carrier($oldCarrierId);

        if ($oldCarrier->active !== $carrier->active && $carrier->id) {
            $this->trackCarrierActiveChanges($carrier->id);
        }
    }

    private function trackCarrierActiveChanges(int $carrierId): void
    {
        $carrier = new \Carrier($carrierId);
        $eventAction = $carrier->active ? 'Enabled' : 'Disabled';
        $account = (new AccountsService())->getAccountsContext($this);

        $segment = new Segment($this);
        $segment->setMessage('[SHI] Carrier ' . $eventAction);
        $segment->setOptions([
            'date' => date('Ymd'),
            'enabled' => (bool) $carrier->active,
            'email' => !empty($account['user']['email']) ? $account['user']['email'] : null,
            'carrier_name' => $carrier->name,
            'mbe_shipping_service' => explode('_', $carrier->external_module_name)[1],
        ]);
        $segment->track();
    }

    /**
     * Only for 1.7.6
     *
     * @param Order[] $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderContentShip(array $params)
    {
        if (!$this->canDisplayShippingTab((int) $params['order']->id)) {
            return '';
        }

        $assets = $this->getAssetsPath();

        if (empty($this->context->smarty)) {
            return '';
        }

        $this->smarty->assign([
            'appCss' => $assets['appCss'],
            'appLink' => $assets['appLink'],
        ]);

        Media::addJsDef([
            'shipmentDetail' => $this->renderOrderDetail(new Order($params['order']->id)),
        ]);

        return $this->display(__DIR__, '/views/templates/admin/shipping.tpl');
    }

    /**
     * @return array<string, mixed>
     */
    public function renderOrderDetail(Order $order)
    {
        $deliveryDetails = new Address((int) ($order->id_address_delivery));
        $totalCartPackageWeight = (float) (new Cart($order->id_cart))->getTotalWeight();
        $maxWeightPerPackage = floatval(is_numeric((new Configuration())->get('PS_SHIPPING_MAX_WEIGHT_PER_PACKAGE', 0)) ? (new Configuration())->get('PS_SHIPPING_MAX_WEIGHT_PER_PACKAGE', 0) : 0);
        $trackingNumber = (new OrderCarrier($order->id))->tracking_number;
        $carrier = new Carrier((int) $order->id_carrier);

        return [
            'deliveryMode' => $carrier->name,
            'numberOfPackages' => (int) ceil($totalCartPackageWeight / $maxWeightPerPackage),
            'totalCartWeight' => $totalCartPackageWeight,
            'maxWeightPerPackage' => $maxWeightPerPackage,
            'shippingDate' => $order->delivery_date,
            'deliveryAddress' => [
                'recipient' => $this->buildRecipient($deliveryDetails, $order->getCustomer()),
                'shipment' => $this->buildShipmentDetail($order, $carrier->external_module_name, $deliveryDetails),
            ],
            'contextPsAccounts' => (new AccountsService())->getAccountsContext($this),
            'tokenPsAccounts' => (new AccountsService())->getPsAccountToken($this),
            'psxShippingApiUrl' => $this->getApiUrl(),
            'defaultIsoCode' => $this->context->language->iso_code ?? 'en',
            'orderId' => $order->id,
            'trackingNumber' => $trackingNumber,
            'saveTrackingNumberControllerLink' => (new PrestaShopAdapter($this))->generateRoute('save_tracking_number', ['route' => 'save_tracking_number']),
        ];
    }

    /**
     * @param array<string, string> $params
     *
     * Only for 1.7.7+
     *
     * @return string
     */
    public function hookDisplayAdminOrderTabContent(array $params)
    {
        if (!$this->canDisplayShippingTab((int) $params['id_order'])) {
            return '';
        }

        /** @var Router $router */
        $router = $this->get('router');
        $order = new Order((int) $params['id_order']);
        $assets = $this->getAssetsPath();

        $shipmentDetail = $this->renderOrderDetail($order);
        $shipmentDetail['saveTrackingNumberControllerLink'] = $router->generate('save_tracking_number');

        return $this->render('@Modules/psshipping/views/templates/admin/shipping.html.twig', [
            'appCss' => $assets['appCss'],
            'appLink' => $assets['appLink'],
            'shipmentDetail' => $shipmentDetail,
            'params' => $order,
        ]);
    }

    /**
     * @param Address $address
     * @param Customer $customer
     *
     * @return array<string, string>
     */
    private function buildRecipient($address, $customer)
    {
        $state = '';

        if ($address->id_state) {
            $state = (new StateCore($address->id_state))->iso_code;
        }

        return [
            'name' => empty($address->company) ? $customer->firstname . ' ' . $customer->lastname : $address->company,
            'address' => $address->address1,
            'address2' => $address->address2,
            'phone' => $address->phone,
            'zipCode' => $address->postcode,
            'city' => $address->city,
            'state' => $state,
            'country' => (new CountryCore($address->id_country))->iso_code,
            'email' => $customer->email,
        ];
    }

    /**
     * @param Order $order
     * @param string $carrierName
     * @param Address $address
     *
     * @return array<string, array<int<0, max>|string,float|int>|int|string>
     */
    private function buildShipmentDetail($order, $carrierName, $address)
    {
        $configuration = new Configuration();
        $weight = 0;
        $width = 0;
        $height = 0;
        $length = 0;
        $productsWeight = [];

        if (is_numeric($configuration->get('PS_SHIPPING_MAX_WEIGHT_PER_PACKAGE'))) {
            $weight = floatval($configuration->get('PS_SHIPPING_MAX_WEIGHT_PER_PACKAGE'));
        }
        if (is_numeric($configuration->get('PS_SHIPPING_MAX_WIDTH_PER_PACKAGE'))) {
            $width = floatval($configuration->get('PS_SHIPPING_MAX_WIDTH_PER_PACKAGE'));
        }
        if (is_numeric($configuration->get('PS_SHIPPING_MAX_HEIGHT_PER_PACKAGE'))) {
            $height = floatval($configuration->get('PS_SHIPPING_MAX_HEIGHT_PER_PACKAGE'));
        }
        if (is_numeric($configuration->get('PS_SHIPPING_MAX_LENGTH_PER_PACKAGE'))) {
            $length = floatval($configuration->get('PS_SHIPPING_MAX_LENGTH_PER_PACKAGE'));
        }

        foreach ($order->getProducts() as $product) {
            if ($product['product_quantity'] > 1) {
                for ($i = 1; $i < $product['product_quantity']; ++$i) {
                    $productsWeight[] = (float) $product['weight'];
                }
            }
            $productsWeight[] = (float) $product['weight'];
        }

        $serviceId = $carrierName === 'psshipping_express' ? 4 : 2;

        return [
            'orderDate' => $order->date_upd,
            'service' => $serviceId,
            'packageType' => 'GENERIC',
            'description' => empty($address->address2) ? '-' : $address->address2,
            'productsWeight' => $productsWeight,
            'defaultConfiguration' => [
                'weight' => $weight,
                'width' => $width,
                'height' => $height,
                'length' => $length,
            ],
        ];
    }

    /**
     * @return bool
     */
    private function canDisplayShippingTab(int $idOrder)
    {
        $order = new Order($idOrder);
        $shipment = new Carrier((int) $order->getShipping()[0]['id_carrier']);

        return in_array($shipment->external_module_name, CarrierService::CARRIERS_CREATED_BY_MODULE);
    }

    /**
     * Only for 1.7.6
     *
     * @param Order[] $params
     */
    public function hookDisplayAdminOrderTabShip(array $params): string
    {
        if (!$this->canDisplayShippingTab((int) $params['order']->id)) {
            return '';
        }

        $psImage = $this->getPathUri() . '/views/img/prestashop.svg';

        if ($this->context->smarty === null) {
            return '';
        }

        $this->context->smarty->assign([
            'psImage' => $psImage,
        ]);

        return $this->display(__DIR__, '/views/templates/admin/shipping-link.tpl');
    }

    /**
     * Only for 1.7.7+
     *
     * @param array<string, string> $params
     */
    public function hookDisplayAdminOrderTabLink(array $params): string
    {
        if (!$this->canDisplayShippingTab((int) $params['id_order'])) {
            return '';
        }

        $psImage = $this->getPathUri() . '/views/img/prestashop.svg';

        return $this->render('@Modules/psshipping/views/templates/admin/shipping-link.html.twig', [
            'psImage' => $psImage,
        ]);
    }

    /**
     * Render a twig template.
     *
     * @param string $template
     * @param array<string, array<string,mixed>|Order|string> $params
     *
     * @return string
     */
    private function render(string $template, array $params = []): string
    {
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            $twig = $this->getTwig();
        } else {
            $twig = $this->get('twig');
        }

        if (!$twig instanceof Twig_Environment) {
            throw new PrestaShopException('Twig service not found');
        }

        return $twig->render($template, $params);
    }

    /**
     * @return array<string, string>
     */
    private function getAssetsPath()
    {
        $pathAppBuilded = '';
        $pathAssetsBuilded = '';

        if (!$this->isDevEnvironment()) {
            $pathAppBuilded = $this->getCdnUrl() . '/js/psshipping-web-component.js';
            $pathAssetsBuilded = $this->getCdnUrl() . '/css/style.css';
        } else {
            $pathAppBuilded = $this->getPathUri() . '/views/build/js/psshipping-web-component.js?v=' . time();
            $pathAssetsBuilded = $this->getPathUri() . '/views/build/css/style.css';
        }

        return [
            'appCss' => $pathAssetsBuilded,
            'appLink' => $pathAppBuilded,
        ];
    }

    /**
     * @return bool
     */
    private function isPhpVersionCompliant()
    {
        return 70200 <= PHP_VERSION_ID;
    }
}
