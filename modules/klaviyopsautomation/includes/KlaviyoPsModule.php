<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use KlaviyoPs\Classes\KlaviyoCouponUsageLimitType;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\HooksHandler;
use KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService;
use KlaviyoPs\Classes\KlaviyoServices\CustomerEventService;
use KlaviyoPs\Classes\KlaviyoServices\OrderEventService;
use KlaviyoPs\Classes\KlaviyoUtils;
use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoPs\Classes\PrestashopServices\ContextService;
use KlaviyoPs\Classes\PrestashopServices\CustomerService;
use KlaviyoPs\Classes\PrestashopServices\LoggerService;
use KlaviyoPs\Classes\PrestashopServices\OrderService;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;

class KlaviyoPsModule extends Module
{
    /** @var string[] */
    const CONFIG_KEYS = [
        'KLAVIYO_PUBLIC_API',
        'KLAVIYO_PRIVATE_API',
        'KLAVIYO_IS_SYNCING_SUBSCRIBERS',
        'KLAVIYO_SUBSCRIBER_LIST',
        'KLAVIYO_ORDER_STATUS_MAP',
        'KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE',
        'KLAVIYO_REAL_TIME_EVENT_ENABLE',
        'KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS',
        'KLAVIYO_SMS_SUBSCRIBER_LIST',
        'KLAVIYO_SMS_SUBSCRIBE_TRIGGER',
        'KLAVIYO_SMS_CONSENT_LABEL',
        'KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT',
        'KLAVIYO_COUPON_USAGE_LIMIT_TYPE',
        'KLAVIYO_BIS_ENABLED',
    ];

    const ADMIN_CONTROLLERS = array(
        array(
            'name' => 'Klaviyo',
            'visible' => true,
            'class_name' => 'AdminKlaviyoPsConfig',
            'parent_class_name' => 'CONFIGURE',
            'icon' => 'trending_up',
        ),
    );

    /** @var string[] Custom checkout module controller page names and corresponding input selectors. */
    const CUSTOM_CHECKOUTS_SELECTORS = array(
        'module-supercheckout-supercheckout' => 'input[name="supercheckout_email"]',
        'module-thecheckout-order' => 'input[type="email"]',
        'module-ets_onepagecheckout-order' => 'input[type="email"]',
    );

    /**
     * List of transactional email templates that will not be sent
     *
     * @var string[]
     */
    const STATIC_TRANSACTIONAL_EMAIL_TEMPLATES = [
        'order_conf',
        'download_product',
        // blocking offline payment methods transactional emails
        'cheque',
        'bankwire',
        'cashondelivery',
    ];

    /**
     * List of back in stock email templates that will not be sent
     */
    const BACK_IN_STOCK_EMAIL_TEMPLATES = [
        'productoutofstock',
    ];

    /**
     * @var static
     */
    protected static $instance = null;

    /**
     * It's a Symfony service container
     * We use our service container when it's impossible to use the service container of PrestaShop
     * It's better to use the service container of PrestaShop when it's possible
     *
     * @var ServiceContainer
     */
    protected $klaviyoContainer = null;

    /**
     * During the build process, Webpack generates a manifest.json file that includes a list of all the output files created.
     * Each output file has a unique versioned name, which allows use to get the correct filename.
     *
     * @var array|null
     */
    protected $cacheManifest = null;

    /**
     * Klaviyo constructor.
     */
    public function __construct()
    {
        self::$instance = $this;

        $this->tab = 'advertising_marketing';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->description = $this->l('Klaviyo module to integrate PrestaShop with Klaviyo.', 'klaviyopsmodule');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?', 'klaviyopsmodule');

        if (!Configuration::get('KLAVIYO')) {
            $this->warning = $this->l('No name provided', 'klaviyopsmodule');
        }
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        // Theoretically, is not supposed to be null.
        // However, if this is not the case, then this will make sure of it.
        if (self::$instance === null) {
            // This is a workaround to retrieve the current module name
            $modulePath = dirname(__FILE__, 2);
            $moduleName = basename($modulePath);

            self::$instance = Module::getInstanceByName($moduleName);
        }

        return self::$instance;
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (
            Configuration::hasKey('KLAVIYO') &&
            Configuration::get('KLAVIYO') !== $this->name
        ) {
            $this->_errors[] = $this->l('You cannot install the Klaviyo addon and the PrestaShop Automation addon simultaneously.');

            return false;
        }

        // Register hooks first to set up custom webservices so we can set permissions to custom resources.
        return parent::install() &&
            Configuration::updateValue('KLAVIYO', $this->name) &&
            Configuration::updateValue('KLAVIYO_REAL_TIME_EVENT_ENABLE', 0) &&
            Configuration::updateValue('KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE', 0) &&
            Configuration::updateValue('KLAVIYO_COUPON_USAGE_LIMIT_TYPE', KlaviyoCouponUsageLimitType::LIMIT_PREFIX) &&
            Configuration::updateValue('KLAVIYO_BIS_ENABLED', 0) &&
            $this->registerControllersAndHooks() &&
            $this->setupWebservice() &&
            $this->installTabs();
    }

    /**
     * Install all Tabs.
     *
     * @return bool
     */
    public function installTabs()
    {
        foreach (static::ADMIN_CONTROLLERS as $adminTab) {
            if (false === $this->installTab($adminTab)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install Tab.
     *
     * @param array $tabData
     * @return bool
     */
    public function installTab(array $tabData)
    {
        $tab = new Tab();
        $tab->id_parent = Tab::getIdFromClassName($tabData['parent_class_name']);
        $tab->name = array_fill_keys(array_values(Language::getIDs(false)), $tabData['name']);
        $tab->class_name = $tabData['class_name'];
        // Makes the module accessible in the Admin Controller, see ModuleAdminControllerCore constructor.
        $tab->module = $this->name;
        $tab->active = (bool) $tabData['visible'];
        $tab->icon = $tabData['icon'];

        return $tab->save();
    }

    /**
     * Turn on webservice, create token and set permissions.
     *
     * @return bool
     */
    protected function setupWebservice()
    {
        if (
            !Configuration::updateValue('PS_WEBSERVICE', true) ||
            !$this->createWebserviceKey()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Auto-setup webservice key and permissions for API access.
     *
     * @return bool
     */
    protected function createWebserviceKey()
    {
        $existingKey = Configuration::get('KLAVIYO_WEBSERVICE_KEY');

        // If we've already created a key, just pass.
        if (
            $existingKey &&
            WebserviceKey::keyExists($existingKey)
        ) {
            return true;
        }

        // Create and set the WebserviceKey object properties
        $webservice = new WebserviceKey();
        $key = Tools::passwdGen(32);
        $webservice->key = $key;
        $webservice->description = 'Klaviyo webservice key';

        // Save webservice key
        if (
            !$webservice->add() ||
            !Configuration::updateValue('KLAVIYO_WEBSERVICE_ID', $webservice->id) ||
            !Configuration::updateValue('KLAVIYO_WEBSERVICE_KEY', $webservice->key)
        ) {
            $this->_errors[] =
                $this->l('It was not possible to install the Klaviyo module: webservice key creation error.', 'klaviyopsmodule');
            return false;
        }

        // Set webservice key permissions
        if (!$this->setWebservicePermissionsForAccount($webservice->id, $this->getWebservicePermissions())) {
            $this->_errors[] =
                $this->l('It was not possible to install the Klaviyo module: webservice key permissions setup error.', 'klaviyopsmodule');
            return false;
        }
        return true;
    }

    /**
     * Get Webservice permissions for Klaviyo Webservice key.
     *
     * @return string[][]
     */
    protected function getWebservicePermissions()
    {
        return array(
            'klaviyo' => array('GET', 'PUT', 'POST', 'DELETE', 'HEAD'),
        );
    }

    /**
     * Set permissions for resources on auto-created webservice token.
     *
     * HACK - Sadly, the built-in WebserviceKey::setPermissionForAccount method doesn't work in this flow. We register
     * our custom 'klaviyo' resource earlier in the installation process which should get picked up by the
     * addWebserviceResources hook in WebserviceRequest::getResources but it doesn't, our method to handle that hook
     * doesn't fire during installation (works fine later). So this method essentially does the same permission setting
     * but doesn't match against the available resources because our custom resource isn't available yet. We won't set
     * permissions if we can't register the hook because installation will fail earlier.
     *
     * @param $webserviceId
     * @param $permissionsToSet
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function setWebservicePermissionsForAccount($webserviceId, $permissionsToSet)
    {
        $success = true;
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'webservice_permission` WHERE `id_webservice_account` = ' . (int) $webserviceId;
        if (!Db::getInstance()->execute($sql)) {
            $success = false;
        }
        if (isset($permissionsToSet)) {
            $permissions = array();
            $methods = array('GET', 'PUT', 'POST', 'DELETE', 'HEAD');
            foreach ($permissionsToSet as $resource_name => $resource_methods) {
                foreach ($resource_methods as $method_name) {
                    if (in_array($method_name, $methods)) {
                        $permissions[] = array($method_name, $resource_name);
                    }
                }
            }
            $account = new WebserviceKey($webserviceId);
            if ($account->deleteAssociations() && $permissions) {
                $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'webservice_permission` (`id_webservice_permission` ,`resource` ,`method` ,`id_webservice_account`) VALUES ';
                foreach ($permissions as $permission) {
                    $sql .= '(NULL , \'' . pSQL($permission[1]) . '\', \'' . pSQL($permission[0]) . '\', ' . (int) $webserviceId . '), ';
                }
                $sql = rtrim($sql, ', ');
                if (!Db::getInstance()->execute($sql)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Register controllers and hooks.
     *
     * @return bool
     */
    public function registerControllersAndHooks()
    {
        return $this->registerHook('moduleRoutes') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('actionCustomerAccountUpdate') &&
            // Custom hook from ps_emailsubscription module (default newsletter subscribe form).
            $this->registerHook('actionNewsletterRegistrationAfter') &&
            $this->registerHook('addWebserviceResources') &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('actionEmailSendBefore') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('additionalCustomerAddressFields') &&
            $this->registerHook('actionSubmitCustomerAddressForm') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('actionFrontControllerInitAfter') &&
            $this->registerHook('actionObjectMailAlertAddAfter');
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (
            !parent::uninstall() ||
            !Configuration::deleteByName('KLAVIYO') ||
            !$this->deleteKlaviyoConfigurationKeys() ||
            !$this->uninstallTabs() ||
            !$this->unregisterControllersAndHooks()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Delete module configuration keys.
     *
     * @return bool
     */
    public function deleteKlaviyoConfigurationKeys()
    {
        return count(array_filter(self::CONFIG_KEYS, 'Configuration::deleteByName')) == count(self::CONFIG_KEYS);
    }

    /**
     * Unregister controllers and hooks on module uninstall.
     *
     * @return bool
     */
    public function unregisterControllersAndHooks()
    {
        return $this->unregisterHook('moduleRoutes') &&
            $this->unregisterHook('actionCustomerAccountAdd') &&
            $this->unregisterHook('actionCustomerAccountUpdate') &&
            // Custom hook from ps_emailsubscription module (default newsletter subscribe form).
            $this->unregisterHook('actionNewsletterRegistrationAfter') &&
            $this->unregisterHook('addWebserviceResources') &&
            $this->unregisterHook('actionAdminControllerSetMedia') &&
            $this->unregisterHook('actionFrontControllerSetMedia') &&
            $this->unregisterHook('actionEmailSendBefore') &&
            $this->unregisterHook('actionOrderStatusPostUpdate') &&
            $this->unregisterHook('displayAdminAfterHeader') &&
            $this->unregisterHook('additionalCustomerAddressFields') &&
            $this->unregisterHook('actionSubmitCustomerAddressForm') &&
            $this->unregisterHook('displayOrderConfirmation') &&
            $this->unregisterHook('actionFrontControllerInitAfter') &&
            $this->unregisterHook('actionObjectMailAlertAddAfter');
    }

    /**
     * Uninstall all Tabs.
     *
     * @return bool
     */
    public function uninstallTabs()
    {
        foreach (static::ADMIN_CONTROLLERS as $adminTab) {
            if (false === $this->uninstallTab($adminTab)) {
                $this->_errors[] = 'Failed to uninstall all tabs.';
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall Tab.
     *
     * @param array $tabData
     * @return bool
     */
    public function uninstallTab(array $tabData)
    {
        $tabId = Tab::getIdFromClassName($tabData['class_name']);
        $tab = new Tab($tabId);

        if (false === (bool) $tab->delete()) {
            return false;
        }

        return true;
    }

    /**
     * Validate form and handle requests for the displayForm action.
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminKlaviyoPsConfig'));
    }

    /**
     * Configuration::get() defaults to config values at higher shop scopes if key is not set. This pulls in config
     * values for our form that aren't actually set. Basically undoing this to return null if the specific shop scope
     * does not have a value set.
     *
     * @param $configKey
     * @return bool|string|null
     */
    public function getConfigurationValueOrNull($configKey, $idLang = null)
    {
        // If Multi-store is not active, getContext() returns Shop::CONTEXT_SHOP.
        if (!Shop::isFeatureActive()) {
            return Configuration::get($configKey, $idLang);
        }

        if ($this->context->shop->getContext() === Shop::CONTEXT_SHOP && Configuration::hasKey($configKey, $idLang, null, $this->context->shop->id)) {
            return Configuration::get($configKey, $idLang, null, $this->context->shop->id);
        } elseif ($this->context->shop->getContext() === Shop::CONTEXT_GROUP && Configuration::hasKey($configKey, $idLang, Shop::getContextShopGroupID(true))) {
            return Configuration::get($configKey, $idLang, Shop::getContextShopGroupID(true));
        } elseif ($this->context->shop->getContext() === Shop::CONTEXT_ALL) {
            return Configuration::get($configKey, $idLang);
        }

        return null;
    }

    /**
     * Retrieve the service
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if ($this->klaviyoContainer !== null) {
            return $this->klaviyoContainer->getService($serviceName);
        }

        try {
            // Before PrestaShop 1.7.6, we can't define service in legacy context
            // So, te be able to use the current architecture, we need to use the container adapter
            // https://devdocs.prestashop-project.org/8/modules/concepts/services/#services-in-legacy-environment
            if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
                throw new Exception();
            }

            // Can throw Exception ServiceNotFoundException if there is a cache issue
            $service = $this->get($serviceName);

            // In some case Module::get can return false if the Symfony container is not available (some modules can broke it for example)
            if ($service === false) {
                throw new Exception();
            }

            return $service;
        } catch (Exception $e) {
            $this->klaviyoContainer = new ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );

            return $this->getService($serviceName);
        }
    }

    /**
     * Get path URI to a file located in "dist" folder by using manifest.json
     *
     * @param string $name
     * @return string
     */
    public function getDistPathUri($name)
    {
        $modulePath = $this->getLocalPath();
        $moduleUrl = $this->getPathUri();

        if ($this->cacheManifest === null) {
            $manifestData = file_get_contents("{$modulePath}dist/manifest.json");
            $manifestData = json_decode($manifestData, true);

            $this->cacheManifest = $manifestData;
        }

        $distPath = $this->cacheManifest[$name];

        switch ($this->context->controller->controller_type) {
            case 'admin':
            case 'moduleadmin':
                return "{$moduleUrl}dist{$distPath}";
            default:
                return "modules/{$this->name}/dist{$distPath}";
        }
    }

    /**
     * Setup custom routes.
     *
     * @return array
     */
    public function hookModuleRoutes()
    {
        return [
            'module-klaviyo-reclaim' => [
                'rule' => 'klaviyo/reclaim/cart',
                'keywords' => [],
                'controller' => 'reclaim',
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ]
            ],
            'module-klaviyo-build' => [
                'rule' => 'klaviyo/reclaim/build-reclaim',
                'keywords' => [],
                'controller' => 'buildReclaim',
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ]
            ],
            'module-klaviyo-add-to-cart' => [
                'rule' => 'klaviyo/events/add-to-cart',
                'keywords' => [],
                'controller' => 'addToCart',
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ]
            ]
        ];
    }

    /**
     * Handle actionCustomerAccountAdd hook.
     * @param array $params
     */
    public function hookActionCustomerAccountAdd(array $params)
    {
        $hooksHandler = new HooksHandler($this);
        $hooksHandler->handleActionCustomerAccount(
            $params,
            'Account Created'
        );
    }

    /**
     * Handle actionCustomerAccountUpdate hook.
     * @param array $params
     */
    public function hookActionCustomerAccountUpdate(array $params)
    {
        $hooksHandler = new HooksHandler($this);
        $hooksHandler->handleActionCustomerAccount(
            $params,
            'Account Updated'
        );
    }

    /**
     * Handle actionNewsletterRegistrationAfter hook from ps_emailsubscription module.
     *
     * @param array $params
     */
    public function hookActionNewsletterRegistrationAfter(array $params)
    {
        $hooksHandler = new HooksHandler($this);
        $hooksHandler->handleActionNewsletterSubscription($params);
    }

    /**
     * Handle addWebserviceResources hook. This method needs to return the webservice definition
     * for the new endpoint in order to register it properly in WebserviceRequestCore::getResources().
     *
     * @param array $params
     * @return array[]
     */
    public function hookAddWebserviceResources(array $params)
    {
        $hooksHandler = new HooksHandler($this);
        return $hooksHandler->handleAddWebserviceResources($params);
    }

    /**
     * Handle actionFrontControllerSetMedia hook.
     * Fires on all BackOffice pages.
     * Calls methods to inject javascript & CSS files & dependent data.
     *
     * @param $params
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addCSS(
            $this->getDistPathUri('admin-global.css')
        );
    }

    /**
     * Handle actionFrontControllerSetMedia hook. Fires on all Front Office pages
     * and calls methods to inject javascript files and dependent data.
     *
     * @param $params
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        if ($this->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API')) {
            $this->setupKlaviyoAnalytics();
            $this->setupProductEvents();
            $this->setupStartedCheckout();
        }
    }

    /**
     * Block the sending of transactional emails on the PrestaShop side to manage them on the Klaviyo side.
     * To do this, we block the mail if :
     *  its template is in the STATIC_TRANSACTIONAL_EMAIL_TEMPLATES list
     *  its Order status (which we get from the template and the subject of the mail) is mapped in the module configuration.
     */
    public function hookActionEmailSendBefore($params)
    {
        try {
            // This is not supposed to happen
            if (
                !isset($params['template']) ||
                !isset($params['subject']) ||
                !isset($params['idLang'])
            ) {
                throw new KlaviyoException('Missing data in $params');
            }

            $isNotBlockingTransactionalEmails = (
                (int)Configuration::get('KLAVIYO_REAL_TIME_EVENT_ENABLE') === 0 ||
                (int)Configuration::get('KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE') === 0 ||
                !$this->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API')
            );
            $isBisDisabled = (int)Configuration::get('KLAVIYO_BIS_ENABLED') === 0;

            // We should not block any emails based on the configuration settings.
            if ($isNotBlockingTransactionalEmails && $isBisDisabled) {
                return true;
            }

            $template = $params['template'];
            $subject = $params['subject'];
            $idLang = (int)$params['idLang'];

            // The template corresponds to a transactional OR back in stock email we want to block.
            $templatesToBlock = array_merge(self::STATIC_TRANSACTIONAL_EMAIL_TEMPLATES, self::BACK_IN_STOCK_EMAIL_TEMPLATES);
            if (in_array($template, $templatesToBlock, true)) {
                return false;
            }

            $sql = (new DbQuery())
                ->select('osl.id_order_state')
                ->from('order_state_lang', 'osl')
                ->where('osl.template = "' . pSQL($template) . '"')
                ->where('osl.name = "' . pSQL($subject) . '"')
                ->where('osl.id_lang = ' . $idLang)
            ;

            $orderStates = Db::getInstance()->executeS($sql);

            if (!is_array($orderStates)) {
                return true;
            }

            foreach ($orderStates as $row) {
                $mappedOrderStatus = KlaviyoUtils::getMappedOrderStatusValue(
                    (int)$row['id_order_state']
                );

                if ($mappedOrderStatus !== null) {
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            /** @var LoggerService $logger */
            $logger = $this->getService('klaviyops.prestashop_services.logger');

            $template = (isset($params['template']))
                ? $params['template']
                : 'no_template'
            ;

            $logger->error("An error occured in hookActionEmailSendBefore for {$template} email template");

            return true;
        }
    }

    /**
     * Sending order event in real time to Klaviyo
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        try {
            if (
                !isset($params['id_order'])
            ) {
                throw new KlaviyoException('Missing data in $params');
            }

            if (
                (int)Configuration::get('KLAVIYO_REAL_TIME_EVENT_ENABLE') === 0 ||
                !$this->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API')
            ) {
                return;
            }

            /** @var OrderService $orderService */
            $orderService = $this->getService('klaviyops.prestashop_services.order');
            /** @var OrderEventService $orderEventService */
            $orderEventService = $this->getService('klaviyops.klaviyo_service.order_event');

            $order = new Order((int)$params['id_order']);

            if (!Validate::isLoadedObject($order)) {
                throw new KlaviyoException('Order is not valid');
            }

            $order = $orderService->normalize($order);
            $orderEventService->track($order);
        } catch (Exception $e) {
            /** @var LoggerService $logger */
            $logger = $this->getService('klaviyops.prestashop_services.logger');

            $idOrder = (isset($params['id_order']))
                ? $params['id_order']
                : 0
            ;

            $logger->error("An error occured in hookActionOrderStatusPostUpdate {$idOrder} order");

            return true;
        }
    }

    /**
     * Display SMS Consent to customer address form
     *
     * @param  array $params
     * @return array
     */
    public function HookAdditionalCustomerAddressFields($params)
    {
        if (!isset($params['fields'])) {
            return [];
        }

        $hooksHandler = new HooksHandler($this);
        return $hooksHandler->handleAdditionalCustomerAddressFields($params);
    }

    /**
     * Send SMS Consent to Klaviyo
     *
     * @param  array $params
     * @return void
     */
    public function HookActionSubmitCustomerAddressForm($params)
    {
        $hooksHandler = new HooksHandler($this);
        return $hooksHandler->handleActionSubmitCustomerAddressForm($params);
    }

    /**
     * Send SMS Consent to Klaviyo (Place order)
     *
     * @param  mixed $params
     * @return void
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $hooksHandler = new HooksHandler($this);
        return $hooksHandler->handleDisplayOrderConfirmation($params);
    }

    public function hookActionFrontControllerInitAfter($params)
    {
        if (
            $this->context->controller instanceof CartController &&
            CartRule::isFeatureActive() &&
            Tools::getIsset('addDiscount') &&
            $couponConfigValue = Configuration::get('KLAVIYO_COUPON_USAGE_LIMIT_TYPE')
        ) {
            $cartRuleService = $this->getService('klaviyops.prestashop_services.cart_rule');
            $cart = $this->context->cart;

            switch ($couponConfigValue) {
                case KlaviyoCouponUsageLimitType::LIMIT_PREFIX:
                    $code = trim(Tools::getValue('discount_name', ''));
                    $prefix = $cartRuleService->getCartRuleCodePrefix($code);

                    if ($prefix === null) {
                        return;
                    }

                    $cartRule = $cartRuleService->getCartRuleByPrefix($cart, $prefix);

                    if ($cartRule === null) {
                        return;
                    }

                    $errorMsg = $this->l('This voucher is not combinable with a voucher already in your cart:', 'klaviyopsmodule');
                    $this->context->controller->errors[] = $errorMsg . " $cartRule->name";
                case KlaviyoCouponUsageLimitType::LIMIT_ONE:
                    $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
                    if (count($cartRules) > 0) {
                        $this->context->controller->errors[] = $this->l('Limit of one voucher per cart.', 'klaviyopsmodule');
                    }
            }
        }
    }

    /**
     * Callback for hook that fires after MailAlert resource is created. Supports
     * Back in Stock subscription in Klaviyo.
     *
     * @param $params array
     * @return void
     */
    public function hookActionObjectMailAlertAddAfter($params)
    {
        $hooksHandler = new HooksHandler($this);
        $hooksHandler->handleActionObjectMailAlertAddAfter($params);
    }

    /**
     * Register klaviyo.js and identify javascript code along with customer data if
     * public API key is set.
     */
    protected function setupKlaviyoAnalytics()
    {
        if (!$this->isCheckoutPage()) {
            $this->addIdentifyData();
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-analytics',
                'https://static.klaviyo.com/onsite/js/' . $this->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API') . '/klaviyo.js',
                array(
                    'server' => 'remote',
                    'priority' => 450,
                    'attributes' => 'async'
                )
            );
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-identify',
                $this->getDistPathUri('identify.js'),
                array(
                    'priority' => 451,
                )
            );
        }
    }

    /**
     * Define global js variables of customer for identifying with klaviyo object.
     */
    protected function addIdentifyData()
    {
        try {
            /** @var ContextService $contextService */
            $contextService = $this->getService('klaviyops.prestashop_services.context');
            /** @var CustomerService $customerService */
            $customerService = $this->getService('klaviyops.prestashop_services.customer');
            /** @var CustomerEventService $customerEventService */
            $customerEventService = $this->getService('klaviyops.klaviyo_service.customer_event_service');

            $customer = null;

            if (Validate::isLoadedObject($this->context->customer)) {
                $context = $contextService->normalize(); // Current context
                $customer = $customerService->normalize(
                    $this->context->customer,
                    $context
                );

                $customer = $customerEventService->buildPayloadForJs($customer);
            }

            Media::addJsDef([
                'klCustomer' => $customer,
            ]);
        } catch (Exception $e) {
            /** @var LoggerService $logger */
            $logger = $this->getService('klaviyops.prestashop_services.logger');

            if (Validate::isLoadedObject($this->context->customer)) {
                $logger->error("An error occured in addIdentifyData for customer {$this->context->customer->email}");
            } else {
                $logger->error('An error occured in addIdentifyData');
            }
        }
    }

    /**
     * Setup Viewed Product and Add to Cart events if on Product page.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function setupProductEvents()
    {
        if ($this->context->controller->php_self == 'product') {
            $lang_id = $this->context->language->id;
            $shop_id = $this->context->shop->id;
            $product = new Product(Tools::getValue('id_product'), $full = false, $id_lang = $lang_id, $id_shop = $shop_id);

            // Bail if product no longer exists.
            if (is_null($product->id)) {
                return;
            }

            $this->setupViewedProduct();
            $this->setupAddToCart();
        }
    }

    /**
     * Add viewed product data and register javascript file.
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function setupViewedProduct()
    {
        $this->addViewedProductData();
        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-viewed-product',
            $this->getDistPathUri('viewed-product.js'),
            array(
                'priority' => 460,
            )
        );
    }

    /**
     * Define javascript global for Viewed Product event.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function addViewedProductData()
    {
        $lang_id = $this->context->language->id;
        $shop_id = $this->context->shop->id;
        $product = new Product(Tools::getValue('id_product'), $full = false, $id_lang = $lang_id, $id_shop = $shop_id);
        $product_id = $product->id;

        // Build categories array
        $productCategories = array();
        foreach (Product::getProductCategoriesFull($product_id, $lang_id) as $category) {
            $productCategories[] = $category['name'];
        };

        // Get product URL and allow context to handle language.
        $link = new Link();
        $productLink = $link->getProductLink($product);

        // Get product price without tax. This returns a float while
        // price attribute returns a string with 6 significant digits.
        $price = $product->getPrice(false);

        Media::addJsDef(
            array(
                'klProduct' => array(
                    'ProductName' => $product->name,
                    'ProductID' => $product_id,
                    'SKU' => $product->reference,
                    'Tags' => ProductPayloadService::getProductTagsArray($product_id, $lang_id),
                    'Price' => KlaviyoUtils::formatPrice($price),
                    'PriceInclTax' => KlaviyoUtils::formatPrice($product->getPrice()),
                    'SpecialPrice' => KlaviyoUtils::formatPrice(Product::getPriceStatic($product_id)),
                    'Categories' => $productCategories,
                    'Image' => ProductPayloadService::buildProductImageUrls($product),
                    'Link' => $productLink,
                    'ShopID' => $shop_id,
                    'LangID' => $lang_id,
                    'eventValue' => is_numeric($price) ? $price : 0,
                    'external_catalog_id' => KlaviyoUtils::formatKlaviyoCatalogIdentifier($shop_id, $lang_id),
                    'integration_key' => KlaviyoValue::SERVICE,
                )
            )
        );
    }

    /**
     * Define js object to support custom base URI for added to cart module route.
     * @return void
     */
    protected function addAddedToCartData()
    {
        $addedToCartUrl = rtrim(__PS_BASE_URI__, '/') . '/klaviyo/events/add-to-cart';
        Media::addJsDef(
            [
                'klAddedToCart' => [
                    'url' => $addedToCartUrl
                ]
            ]
        );
    }

    /**
     * Register Added to Cart javascript.
     */
    protected function setupAddToCart()
    {
        $this->addAddedToCartData();
        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-add-to-cart',
            $this->getDistPathUri('add-to-cart.js'),
            array(
                'priority' => 465,
            )
        );
    }

    /**
     * Add started checkout data and register javascript file.
     */
    protected function setupStartedCheckout()
    {
        if ($this->shouldInsertStartedCheckoutJavascript()) {
            $this->addStartedCheckoutData();
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-started-checkout',
                $this->getDistPathUri('started-checkout.js'),
                array(
                    'priority' => 470,
                )
            );
        }
    }

    /**
     * Add javascript definition to DOM for Started Checkout events.
     */
    protected function addStartedCheckoutData()
    {
        $link = $this->context->link;
        if ($link === null) {
            $link = new Link();
        }

        Media::addJsDef(
            array(
                'klStartedCheckout' => array(
                    'cartId' => isset(Context::getContext()->cart->id) ? Context::getContext()->cart->id : false,
                    'email' => $this->context->customer->email,
                    'token' => Tools::getToken(),
                    'baseUrl' => $link->getModuleLink($this->name, 'buildReclaim'),
                    'emailInputSelector' => $this->getCheckoutEmailInputSelector(),
                )
            )
        );
    }

    /**
     * Returns custom checkout email input field selector if not default checkout.
     *
     * @return string
     */
    protected function getCheckoutEmailInputSelector()
    {
        if ($this->isCustomCheckoutPage()) {
            return self::CUSTOM_CHECKOUTS_SELECTORS[$this->context->controller->page_name];
        }

        // Default checkout page selector.
        return '[type="email"]';
    }

    /**
     * Confirm if checkout session is on the Personal Information Step to capture email address and only send
     * event once.
     *
     * @param $sessionData
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    protected function isCheckoutPersonalInfoStep($sessionData)
    {
        // If a customer hasn't been to the checkout page yet, this value in the DB will be NULL. Treat as first step.
        if (!isset($sessionData)) {
            return true;
        }
        $personalInfoStep = $sessionData['checkout-personal-information-step'];
        return $personalInfoStep['step_is_reachable'] && !$personalInfoStep['step_is_complete'];
    }

    /**
     * Confirm if checkout session is on the Addresses step and the customer is Logged in.
     *
     * @param $sessionData
     * @return bool
     */
    protected function isLoggedInAddressesStep($sessionData)
    {
        $addressesStep = $sessionData['checkout-addresses-step'];
        return $this->context->customer->isLogged() && $addressesStep['step_is_reachable'] && !$addressesStep['step_is_complete'];
    }

    /**
     * Confirm page default checkout or one of known custom checkouts for injecting Started Checkout code.
     *
     * @return bool
     */
    protected function isCheckoutPage()
    {
        return $this->isDefaultCheckoutPage() || $this->isCustomCheckoutPage();
    }

    /**
     * Confirm page is Order front controller and checkout page.
     *
     * @return bool
     */
    protected function isDefaultCheckoutPage()
    {
        return $this->context->controller->php_self == 'order' && $this->context->controller->page_name == 'checkout';
    }

    /**
     * Confirm page is a known custom checkout we want to support.
     *
     * @return bool
     */
    protected function isCustomCheckoutPage()
    {
        return (
            property_exists($this->context->controller, 'page_name')
            && in_array($this->context->controller->page_name, array_keys(self::CUSTOM_CHECKOUTS_SELECTORS))
        );
    }

    /**
     * Confirm this is a valid step in the checkout process for injecting Started Checkout code.
     *
     * @return bool
     */
    protected function isCorrectCheckoutStep()
    {
        $cartId = $this->context->cart->id;
        if (!$cartId) {
            return false;
        }

        $select = 'SELECT checkout_session_data ';
        $from = 'FROM ' . _DB_PREFIX_ . 'cart ';
        $where = 'WHERE id_cart = ' . (int) $cartId;

        $sql = $select . $from . $where;
        try {
            $result = Db::getInstance()->ExecuteS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }

        if (!$result || !array_key_exists('checkout_session_data', $result[0])) {
            return false;
        }

        $sessionData = json_decode($result[0]['checkout_session_data'], true);

        return $this->isCheckoutPersonalInfoStep($sessionData) || $this->isLoggedInAddressesStep($sessionData);
    }

    /**
     * Determine if we should inject the Started Checkout javascript page type
     * and the given point in the checkout process.
     *
     * @return bool
     */
    protected function shouldInsertStartedCheckoutJavascript()
    {
        return $this->isCheckoutPage() && $this->isCorrectCheckoutStep();
    }
}
