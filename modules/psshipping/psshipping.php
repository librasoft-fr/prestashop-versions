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

use PrestaShop\Module\Psshipping\Controller\Admin\PsshippingConfigurationController;
use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsService;
use PrestaShop\Module\Psshipping\Domain\Api\Webhook;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierRepository;
use PrestaShop\Module\Psshipping\Domain\Carriers\CarrierService;
use PrestaShop\Module\Psshipping\Domain\Carriers\PickupPoints\PsshippingAddressOrdersRepository;
use PrestaShop\Module\Psshipping\Domain\Legacy\PrestaShopAdapter;
use PrestaShop\Module\Psshipping\Domain\Orders\OrdersRepository;
use PrestaShop\Module\Psshipping\Domain\Segment\Segment;
use PrestaShop\Module\Psshipping\Domain\Shipment\RecipientDTO;
use PrestaShop\Module\Psshipping\Domain\Shipment\RecipientPickupPointDTO;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;
use PrestaShop\Module\Psshipping\Helper\ConfigHelper;
use PrestaShop\Module\Psshipping\Hooks\HookActionObjectCarrierUpdateAfter;
use PrestaShop\Module\Psshipping\Hooks\HookActionValidateOrder;
use PrestaShop\Module\Psshipping\Hooks\HookDisplayCarrierExtraContent;
use PrestaShop\Module\Psshipping\Hooks\HookDisplayHeader;
use PrestaShop\Module\Psshipping\Hooks\HookDisplayOrderConfirmation;
use PrestaShop\Module\Psshipping\Service\DatabaseInstaller;
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

    const MODULE_TECHNICAL_NAME = 'psshipping';
    const MBE_API_SERVICE_ID_CARRIER_STANDARD = 2;
    const MBE_API_SERVICE_ID_CARRIER_EXPRESS = 4;
    const MBE_API_SERVICE_ID_CARRIER_PICKUP = 12;

    const HOOK_LIST = [
        'actionGetAdminOrderButtons',
        'actionValidateOrder',
        'displayHeader',
        'displayAdminOrderTabContent',
        'displayAdminOrderTabLink',
        'displayAdminOrderContentShip',
        'displayAdminOrderTabShip',
        'displayBackOfficeHeader',
        'displayCarrierExtraContent',
        'displayOrderConfirmation',
        'actionObjectCarrierUpdateAfter',
        'actionGetOrderShipments',
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
        $this->version = '2.0.9';
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

    public function install(): bool
    {
        $segment = new Segment($this, true);
        $segment->setMessage('[SHI] PS Shipping Installed');
        $segment->track();

        if (!$this->isPhpVersionCompliant()) {
            $this->_errors[] = $this->trans('This requires PHP 7.2 to work properly. Please upgrade your server configuration.', [], 'Modules.Psshipping.Admin');

            return defined('PS_INSTALLATION_IN_PROGRESS');
        }

        if (!parent::install()) {
            $this->_errors[] = $this->trans('An error occured during the parent installation.', [], 'Modules.Psshipping.Admin');

            return false;
        }

        if (!$this->registerHook(self::HOOK_LIST)) {
            $this->_errors[] = $this->trans('An error occured while trying to register hooks.', [], 'Modules.Psshipping.Admin');

            return false;
        }

        try {
            (new DatabaseInstaller($this))->install();
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();

            return false;
        }

        return true;
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
        $configuration->remove(PsshippingConfigurationController::ONBOARDING_IS_DONE);
        $configuration->remove(PsshippingConfigurationController::MAX_WEIGHT_PER_PACKAGE);
        $configuration->remove(PsshippingConfigurationController::MAX_WIDTH_PER_PACKAGE);
        $configuration->remove(PsshippingConfigurationController::MAX_HEIGHT_PER_PACKAGE);
        $configuration->remove(PsshippingConfigurationController::MAX_LENGTH_PER_PACKAGE);
        $configuration->remove(PsshippingConfigurationController::ORDER_MAPPING_IS_ACTIVATE);
        $configuration->remove(PsshippingConfigurationController::ORDER_STATUS_MAPPING);
        $configuration->remove('PS_SHIPPING_WEBHOOK_SECRET');
        (new Webhook($this))->deleteSvixEndpoint();

        $carrierRepository = new CarrierRepository($this);
        $carrierService = new CarrierService($this, $carrierRepository);
        $carrierService->delete();

        return true;
    }

    public function disable($force_all = false): bool
    {
        $carrierRepository = new CarrierRepository($this);
        $carrierService = new CarrierService($this, $carrierRepository);
        $carrierService->update();

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
        $this->registerHook('actionGetOrderShipments');

        try {
            Tools::redirectAdmin((new PrestaShopAdapter($this))->generateRoute('home', ['route' => 'home']));
        } catch (\Exception $e) {
            if ($e instanceof RouteNotFoundException) {
                throw new PsshippingException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * @param array{
     *   carrier: array{
     *     id: int,
     *   }
     * } $params
     */
    public function hookDisplayCarrierExtraContent(array $params): string
    {
        /** @var HookDisplayCarrierExtraContent $hookDisplayCarrierExtraContent */
        $hookDisplayCarrierExtraContent = $this->getService(HookDisplayCarrierExtraContent::class);

        return $hookDisplayCarrierExtraContent->run($params);
    }

    public function hookDisplayHeader(): void
    {
        /** @var HookDisplayHeader $hookDisplayHeader */
        $hookDisplayHeader = $this->getService(HookDisplayHeader::class);

        $hookDisplayHeader->run();
    }

    /**
     * @param array{
     *   order: Order,
     * } $params
     */
    public function hookDisplayOrderConfirmation(array $params): string
    {
        /** @var HookDisplayOrderConfirmation $hookDisplayOrderConfirmation */
        $hookDisplayOrderConfirmation = $this->getService(HookDisplayOrderConfirmation::class);

        return $hookDisplayOrderConfirmation->run($params);
    }

    /**
     * @param array{
     *   cart: Cart,
     *   order: Order,
     * } $params
     */
    public function hookActionValidateOrder(array $params): void
    {
        /** @var HookActionValidateOrder $hookActionValidateOrder */
        $hookActionValidateOrder = $this->getService(HookActionValidateOrder::class);

        $hookActionValidateOrder->run($params);
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
     * Method that dispatches to the correct service container
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        // Check if it's a module service (dot notation or full namespace)
        $isModuleService =
            strpos($serviceName, 'psshipping.') === 0 ||
            strpos($serviceName, 'PrestaShop\\Module\\Psshipping\\') === 0;

        if ($isModuleService) {
            return $this->serviceContainer->getService($serviceName);
        }

        // Otherwise use PrestaShop’s main container
        return $this->get($serviceName);
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

        if (empty($this->context->smarty)) {
            return '';
        }

        return $this->display(__DIR__, 'views/templates/hook/promoteBanner.tpl');
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return __FILE__;
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
    public function getApiUrl(): string
    {
        /** @var ConfigHelper $config */
        $config = $this->getService('psshipping.helper.config');

        return $config->api_url;
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
        /** @var HookActionObjectCarrierUpdateAfter $hookActionObjectCarrierUpdateAfter */
        $hookActionObjectCarrierUpdateAfter = $this->getService(HookActionObjectCarrierUpdateAfter::class);
        $hookActionObjectCarrierUpdateAfter->run($params);
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

        if (empty($this->context->smarty)) {
            return '';
        }

        Media::addJsDef([
            'shipmentDetail' => $this->renderOrderDetail(new Order($params['order']->id)),
            'contextPsAccounts' => (new AccountsService())->getAccountsContext($this),
        ]);

        return $this->display(__DIR__, 'views/templates/admin/shipping.tpl');
    }

    /**
     * @return array<string, mixed>
     */
    private function renderOrderDetail(Order $order)
    {
        $totalCartPackageWeight = (float) (new Cart($order->id_cart))->getTotalWeight();
        $maxWeightPerPackageConfiguration = (new Configuration())->get(PsshippingConfigurationController::MAX_WEIGHT_PER_PACKAGE, 0);
        $maxWeightPerPackage = is_numeric($maxWeightPerPackageConfiguration) ? (float) $maxWeightPerPackageConfiguration : 0.0;
        $orderCarrierId = $order->getIdOrderCarrier();
        /** @var string|null $trackingNumber */
        $trackingNumber = (new OrderCarrier($orderCarrierId))->tracking_number;
        $carrier = new Carrier((int) $order->id_carrier);

        $recipient = $this->buildRecipient($order);

        /** @var CarrierRepository $carrierRepository */
        $carrierRepository = $this->getService(CarrierRepository::class);
        $carrierMapping = $carrierRepository->getShippingCarriersMapping();
        $carrierType = $carrierMapping[$carrier->id];

        return [
            'deliveryMode' => $carrier->name,
            'numberOfPackages' => (int) ($maxWeightPerPackage > 0 ? ceil($totalCartPackageWeight / $maxWeightPerPackage) : 0),
            'totalCartWeight' => $totalCartPackageWeight,
            'maxWeightPerPackage' => $maxWeightPerPackage,
            'shippingDate' => $order->delivery_date,
            'deliveryAddress' => [
                'recipient' => $recipient->toArray(),
                'shipment' => $this->buildShipmentDetail($order, $carrierType, $recipient),
            ],
            'contextPsAccounts' => (new AccountsService())->getAccountsContext($this),
            'tokenPsAccounts' => (new AccountsService())->getPsAccountToken($this),
            'psxShippingApiUrl' => $this->getApiUrl(),
            'defaultIsoCode' => $this->context->language->iso_code ?? 'en',
            'orderId' => $order->id,
            'trackingNumber' => $trackingNumber ?? '',
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

        $shipmentDetail = $this->renderOrderDetail($order);
        $shipmentDetail['saveTrackingNumberControllerLink'] = $router->generate('save_tracking_number');

        return $this->render('@Modules/psshipping/views/templates/admin/shipping.html.twig', [
            'shipmentDetail' => $shipmentDetail,
            'params' => $order,
            'defaultIsoCode' => $this->context->language->iso_code ?? 'en',
            'contextPsAccounts' => (new AccountsService())->getAccountsContext($this),
        ]);
    }

    /**
     * @param Order $order
     *
     * @return RecipientDTO
     */
    private function buildRecipient(Order $order)
    {
        /** @var CarrierRepository $carrierRepository */
        $carrierRepository = $this->getService(CarrierRepository::class);
        $isOrderUsePickupPointCarrier = $carrierRepository->isPickupCarrierFromMapping((int) $order->id_carrier);

        $customer = new Customer((int) $order->id_customer);
        $customerAddress = new Address((int) $order->id_address_delivery);

        // if the order was made with a pickup point carrier, use the address of the pickup point
        if ($isOrderUsePickupPointCarrier) {
            /** @var PsshippingAddressOrdersRepository $addressRepository */
            $addressRepository = $this->getService(PsshippingAddressOrdersRepository::class);
            $pickupPointAddress = $addressRepository->findOneByIdOrderAndShop((int) $order->id, (int) $order->id_shop);

            if ($pickupPointAddress === null) {
                throw new PsshippingException(sprintf('Cannot find the pickup point address associated to the orderId %s.', $order->id));
            }

            $pickupPointAddress = $pickupPointAddress->getAddress();

            $recipient = new RecipientPickupPointDTO(
                $pickupPointAddress->getCode(),
                $pickupPointAddress->getNetworkCode(),
                $customer->firstname . ' ' . $customer->lastname,
                $pickupPointAddress->getAddress(),
                $pickupPointAddress->getDescription() ?? '',
                $customerAddress->phone,
                $pickupPointAddress->getZipCode(),
                $pickupPointAddress->getCity(),
                $pickupPointAddress->getDepartment(),
                $pickupPointAddress->getCountry(),
                $customer->email
            );
        } else { // otherwise use the address delivery of the order (from the customer)
            $customerCountry = new Country($customerAddress->id_country);

            $recipient = new RecipientDTO(
                empty($customerAddress->company) ? $customer->firstname . ' ' . $customer->lastname : $customerAddress->company,
                $customerAddress->address1,
                $customerAddress->address2,
                $customerAddress->phone,
                $customerAddress->postcode,
                $customerAddress->city,
                (new StateCore($customerAddress->id_state))->iso_code,
                $customerCountry->iso_code,
                $customer->email
            );
        }

        return $recipient;
    }

    /**
     * @param Order $order
     * @param string $carrierType
     * @param RecipientDTO $recipient
     *
     * @return array<string, array<int<0, max>|string,float|int>|int|string>
     */
    private function buildShipmentDetail($order, $carrierType, $recipient)
    {
        $configuration = new Configuration();
        $weight = 0;
        $width = 0;
        $height = 0;
        $length = 0;
        $productsWeight = [];

        if (is_numeric($configuration->get(PsshippingConfigurationController::MAX_WEIGHT_PER_PACKAGE))) {
            $weight = floatval($configuration->get(PsshippingConfigurationController::MAX_WEIGHT_PER_PACKAGE));
        }
        if (is_numeric($configuration->get(PsshippingConfigurationController::MAX_WIDTH_PER_PACKAGE))) {
            $width = floatval($configuration->get(PsshippingConfigurationController::MAX_WIDTH_PER_PACKAGE));
        }
        if (is_numeric($configuration->get(PsshippingConfigurationController::MAX_HEIGHT_PER_PACKAGE))) {
            $height = floatval($configuration->get(PsshippingConfigurationController::MAX_HEIGHT_PER_PACKAGE));
        }
        if (is_numeric($configuration->get(PsshippingConfigurationController::MAX_LENGTH_PER_PACKAGE))) {
            $length = floatval($configuration->get(PsshippingConfigurationController::MAX_LENGTH_PER_PACKAGE));
        }

        foreach ($order->getProducts() as $product) {
            if ($product['product_quantity'] > 1) {
                for ($i = 1; $i < $product['product_quantity']; ++$i) {
                    $productsWeight[] = (float) $product['weight'];
                }
            }
            $productsWeight[] = (float) $product['weight'];
        }

        switch ($carrierType) {
            case CarrierService::CARRIERS_STANDARD:
                $serviceId = self::MBE_API_SERVICE_ID_CARRIER_STANDARD;
                break;
            case CarrierService::CARRIERS_PICKUP:
                $serviceId = self::MBE_API_SERVICE_ID_CARRIER_PICKUP;
                break;
            case CarrierService::CARRIERS_EXPRESS:
                $serviceId = self::MBE_API_SERVICE_ID_CARRIER_EXPRESS;
                break;
            default:
                $serviceId = 0;
                break;
        }

        return [
            'orderDate' => $order->date_upd,
            'service' => $serviceId,
            'packageType' => 'GENERIC',
            'description' => $recipient->address2 ?? '-',
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

        /** @var CarrierRepository $carrierRepository */
        $carrierRepository = $this->getService(CarrierRepository::class);
        $carrierMapping = $carrierRepository->getShippingCarriersMapping();

        $configuration = new Configuration();

        if (
            $configuration->get(PsshippingConfigurationController::MAX_WEIGHT_PER_PACKAGE) === '' ||
            $configuration->get(PsshippingConfigurationController::MAX_WIDTH_PER_PACKAGE) === '' ||
            $configuration->get(PsshippingConfigurationController::MAX_HEIGHT_PER_PACKAGE) === '' ||
            $configuration->get(PsshippingConfigurationController::MAX_LENGTH_PER_PACKAGE) === ''
        ) {
            return false;
        }

        return array_key_exists((int) $order->id_carrier, $carrierMapping);
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

        return $this->display(__DIR__, 'views/templates/admin/shipping-link.tpl');
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
     * @param array<string, string> $params
     *
     * @return array<string, mixed>
     */
    public function hookActionGetOrderShipments(array $params): array
    {
        if (empty($params['id_order'])) {
            return [];
        }

        return (new OrdersRepository($this))->getOrderDetails((int) $params['id_order']);
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
     * @return bool
     */
    private function isPhpVersionCompliant()
    {
        return 70200 <= PHP_VERSION_ID;
    }
}
