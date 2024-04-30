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

use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\KlaviyoApiWrapper;
use KlaviyoPs\Classes\BusinessLogicServices\OrderPayloadService;
use KlaviyoPs\Classes\KlaviyoServices\CouponGeneratorService;
use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoPs\Classes\UtilServices\CsvService;
use KlaviyoPs\Classes\UtilServices\EnvService;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Installer;
use KlaviyoPs\Classes\KlaviyoUtils;

class AdminKlaviyoPsConfigController extends ModuleAdminController
{
    const PUBLIC_KEY_VALIDATION_REGEX = '/^[a-zA-Z0-9]{6}$/';
    const PRIVATE_KEY_VALIDATION_REGEX = '/^(pk_)[a-zA-Z0-9]{34}$/';

    /** @var string Minimum version compatible for subscribing profiles using default newsletter form. */
    const PS_EMAILSUBSCRIPTION_MIN_VERSION = '2.6.0';

    public function __construct()
    {
        // Bootstrap set to true to utilize default admin controller styling.
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        $allValues = Tools::getAllValues();

        if (Tools::isSubmit('submit' . $this->module->name)) {
            $this->validateAndSaveConfig('validateConfigValues', 'saveFormKlaviyoValues', $allValues);
        } elseif (Tools::isSubmit('submit' . $this->module->name . 'OrderStatus')) {
            $this->validateAndSaveConfig('validateOrderStatusMapValues', 'saveFormOrderStatusMapValues', $allValues);
        } elseif (Tools::isSubmit('submit' . $this->module->name . 'CouponsGenerator')) {
            $this->validateAndGenerateCoupons();
        }

        if (!$this->module->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API')) {
            $createAccountUrl = 'https://www.klaviyo.com/signup/prestashop?utm_medium=app-store&utm_source=platform-partner&utm_campaign=';
            /** @var EnvService $envService */
            $envService = $this->module->getService('klaviyops.util_services.env');
            if ($envService->get('IS_EDITION_MODE')) {
                $createAccountUrl .= KlaviyoValue::UTM_CAMPAIGN_EDITION;
            } else {
                $createAccountUrl .= KlaviyoValue::UTM_CAMPAIGN_PROJECT;
            }

            $text_question = $this->l('Don\'t have a Klaviyo account?');
            $text_create_account = $this->l('Create your account here.');

            $this->informations[] = $text_question . sprintf(' <a target="_blank" href="%s">', $createAccountUrl) . $text_create_account . '</a>';
        }

        $this->informations[] = sprintf(
            $this->l('For step-by-step instructions on how to integrate Prestashop with Klaviyo, refer to our %shelp documentation.%s'),
            '<a target="_blank" href="https://help.klaviyo.com/hc/en-us/articles/360054551492">',
            '</a>'
        );

        // Prompt to submit order status mapping if default has not been accepted on initial setup.
        if (!$this->module->getConfigurationValueOrNull('KLAVIYO_ORDER_STATUS_MAP')) {
            $this->warnings[] = $this->l('Please complete the order status mapping form or accept the default values.');
        }

        // Display warning if php running as CGI to help avoid auth errors
        if (KlaviyoUtils::shouldWarnCGI()) {
            $this->errors[] = $this->l('It appears you are running PHP as a CGI. Please follow the ')
                . '<a target="_blank" href="https://help.klaviyo.com/hc/en-us/articles/15311862315803">'
                . $this->l('instructions here')
                . '</a>'
                . $this->l(' to ensure Klaviyo can access your API.');
        }

        $this->content .= $this->renderContent();

        parent::initContent();
    }

    /**
     * @return string
     */
    public function renderContent()
    {
        $modulePath = $this->module->getLocalPath();

        // use PrestaShop Design System only for PrestaShop 1.7.8 and higher
        if (version_compare(_PS_VERSION_, '1.7.8', '>=')) {
            $this->addCSS(
                $this->module->getDistPathUri('admin-config.css')
            );
        }

        $this->context->smarty->assign([
            'psAccounts' => $this->renderPsAccounts(),
            'form' => $this->renderForm(),
            'orderStatusMapForm' => $this->renderOrderStatusMapForm(),
            'couponsGenerator' => $this->renderCouponsGenerator(),
            'chunkVendorJs' => $this->module->getDistPathUri('chunk-vendors.js'),
            'adminConfigJs' => $this->module->getDistPathUri('admin-config.js'),
        ]);

        return $this->context->smarty->fetch("{$modulePath}views/templates/admin/config.tpl");
    }

    /**
     * @return string
     */
    public function renderPsAccounts()
    {
        /** @var EnvService $envService */
        $envService = $this->module->getService('klaviyops.util_services.env');
        /** @var Installer $psAccountsInstaller */
        $psAccountsInstaller = $this->module->getService('klaviyops.ps_accounts.installer');
        /** @var PsAccounts $psAccountsFacade */
        $psAccountsFacade = $this->module->getService('klaviyops.ps_accounts.facade');

        if (!$envService->get('IS_EDITION_MODE')) {
            return '';
        }

        $modulePath = $this->module->getLocalPath();
        $psAccountsError = false;

        try {
            $psAccountsInstaller->install();

            $psAccountsService = $psAccountsFacade->getPsAccountsService();
            $contextPsAccounts = $psAccountsFacade
                ->getPsAccountsPresenter()
                ->present($this->module->name)
            ;

            Media::addJsDef([
                'contextPsAccounts' => $contextPsAccounts,
            ]);

            $this->context->smarty->assign([
                'urlAccountsCdn' => $psAccountsService->getAccountsCdn(),
            ]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $psAccountsError = true;

            if ($e instanceof ModuleNotInstalledException) {
                $errorMessage = $this->l('You must install PrestaShop Accounts');
            } elseif ($e instanceof ModuleVersionException) {
                $errorMessage = $this->l('You must update PrestaShop Accounts');
            }

            $this->errors[] = $errorMessage;
        }

        $this->context->smarty->assign([
            'vueData' => [
                'psAccountsError' => $psAccountsError,
            ],
        ]);

        return $this->context->smarty->fetch("{$modulePath}views/templates/admin/ps_accounts.tpl");
    }

    /**
     * @return string
     */
    public function renderForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');
        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Klaviyo Public API Key'),
                    'placeholder' => 'Aa1234',
                    'id' => 'kl_public_key',
                    'name' => 'KLAVIYO_PUBLIC_API',
                    'value' => Configuration::get('KLAVIYO_PUBLIC_API'),
                    'size' => 6,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Klaviyo Private API Key'),
                    'placeholder' => 'pk_111222333444555666777888999',
                    'id' => 'kl_private_key',
                    'name' => 'KLAVIYO_PRIVATE_API',
                    'value' => Configuration::get('KLAVIYO_PRIVATE_API'),
                    'size' => 50,
                    'required' => true,
                    'desc' => '<a target="_blank" href="https://help.klaviyo.com/hc/en-us/articles/115005062267-Manage-Your-Account-s-API-Keys">Need help finding your API keys?</a>'
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Send real-time order events to Klaviyo'),
                    'desc' => $this->l('Send real-time order events to Klaviyo for the following order statuses: Placed Order, Fulfilled Order, Canceled Order, Refunded Order. These events will be labeled "Transactional" in Klaviyo.'),
                    'name' => 'KLAVIYO_REAL_TIME_EVENT_ENABLE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Turn off PrestaShop-generated transactional order emails'),
                    'desc' => $this->l('For the above order statuses, block associated transactional emails sent by PrestaShop. This option is for merchants who wish to send transactional order emails with Klaviyo.'),
                    'name' => 'KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE',
                    'disabled' => (int)Configuration::get('KLAVIYO_REAL_TIME_EVENT_ENABLE') === 0,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Sync subscribers to a list in Klaviyo'),
                    'name' => 'KLAVIYO_IS_SYNCING_SUBSCRIBERS',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        // Fetch lists for Account.
        $lists = [];
        if (
            $this->module->getConfigurationValueOrNull('KLAVIYO_PRIVATE_API')
            && $this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SUBSCRIBERS')
        ) {
            try {
                $api = new KlaviyoApiWrapper();
                $lists = $api->getLists();
            } catch (KlaviyoException $e) {
                $this->errors[] = $this->l(
                    'There was an error accessing lists for account: ' . Configuration::get('KLAVIYO_PUBLIC_API')
                );
            }
        }

        // Ensure we have an array and lists in it.
        if (is_array($lists) && !empty($lists) && $this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SUBSCRIBERS')) {
            // Add default value and "placeholder" option. Defaults to null in db if saved.
            $list_arr = [
                array(
                    'id_option' => '',
                    'name' => $this->l('Select a list'),
                    'value' => '',
                )
            ];
            foreach ($lists as $list) {
                $list_arr[] = array(
                    'id_option' => $list['id'],
                    'name' => $list['attributes']['name'],
                    'value' => $list['id'],
                    'default' => array(
                        'value' => $list['id'],
                        'label' => 'list'
                    ),
                );
            }

            // TODO: We might want to allow someone to reset this value to null.
            // TODO: Provide option whether to subscribe use members or subscribe endpoint.
            // Build form to select Klaviyo list.
            $fieldsForm[]['form'] = [
                'legend' => [
                    'title' => $this->l('Lists'),
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => 'Klaviyo Subscriber List',
                        'name' => 'KLAVIYO_SUBSCRIBER_LIST',
                        'required' => false,
                        'options' => [
                            'query' => $list_arr,
                            'id' => 'id_option',
                            'name' => 'name'
                        ],
                        'desc' => $this->l('Klaviyo will adhere to the double opt-in settings for the selected list.'),
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ];

            // Check version of ps_emailsubscription module and display notice if incompatible for list subscription.
            if (!$this->isPsEmailsubscriptionCompatible()) {
                $fieldsForm[1]['form']['warning'] = $this->l(sprintf(
                    'If you want to subscribe profiles to a Klaviyo list using the
                    PrestaShop \'Newsletter Subscription\' module, please make sure the
                    module is enabled and at least version %s or higher.',
                    self::PS_EMAILSUBSCRIPTION_MIN_VERSION
                ));
            }
        }

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->module->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->module->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->module->name . '&save' . $this->module->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current values
        $helper->fields_value['KLAVIYO_PUBLIC_API'] = $this->module->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API');
        $helper->fields_value['KLAVIYO_PRIVATE_API'] = $this->module->getConfigurationValueOrNull('KLAVIYO_PRIVATE_API');
        $helper->fields_value['KLAVIYO_SUBSCRIBER_LIST'] = $this->module->getConfigurationValueOrNull('KLAVIYO_SUBSCRIBER_LIST');
        $helper->fields_value['KLAVIYO_REAL_TIME_EVENT_ENABLE'] = $this->module->getConfigurationValueOrNull('KLAVIYO_REAL_TIME_EVENT_ENABLE');
        $helper->fields_value['KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE'] = $this->module->getConfigurationValueOrNull('KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE');
        $helper->fields_value['KLAVIYO_IS_SYNCING_SUBSCRIBERS'] = $this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SUBSCRIBERS');

        $helper->fields_value['KLAVIYO_LANGUAGE'] = Configuration::get('KLAVIYO_LANGUAGE');
        return $helper->generateForm($fieldsForm);
    }

    /**
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function renderOrderStatusMapForm()
    {
        $orderStates = OrderState::getOrderStates($this->context->language->id);
        $fieldsForm[]['form'] = array(
            'legend' => array('title' => $this->l('Order status mapping')),
            'description' => $this->l(
                'Select one or more order statuses to map to Klaviyo order related events. For example, Klaviyo can
                record a Fulfilled Order when an order has the status of "Shipped".'
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'multiple' => true,
                    'label'    => $this->l('Status(es) for Placed Order event'),
                    'name'     => 'klaviyops-statuses-placed',
                    'id'       => 'id_order_state_placed',
                    'size'     => count($orderStates),
                    'options'  => array(
                        'query' => $orderStates,
                        'id'    => 'id_order_state',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'     => 'select',
                    'multiple' => true,
                    'label'    => $this->l('Status(es) for Refunded Order event'),
                    'name'     => 'klaviyops-statuses-refunded',
                    'id'       => 'id_order_state_refunded',
                    'size'     => count($orderStates),
                    'options'  => array(
                        'query' => $orderStates,
                        'id'    => 'id_order_state',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'     => 'select',
                    'multiple' => true,
                    'label'    => $this->l('Status(es) for Canceled Order event'),
                    'name'     => 'klaviyops-statuses-canceled',
                    'id'       => 'id_order_state_canceled',
                    'size'     => count($orderStates),
                    'options'  => array(
                        'query' => $orderStates,
                        'id'    => 'id_order_state',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'     => 'select',
                    'multiple' => true,
                    'label'    => $this->l('Status(es) for Fulfilled Order event'),
                    'name'     => 'klaviyops-statuses-fulfilled',
                    'id'       => 'id_order_state_fulfilled',
                    'size'     => count($orderStates),
                    'options'  => array(
                        'query' => $orderStates,
                        'id'    => 'id_order_state',
                        'name'  => 'name',
                    ),
                ),
            ),
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        );
        $helper = new HelperForm();
        // Module, token and currentIndex
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->module->name;

        // Language
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = $this->context->language->id;

        // Title and toolbar
        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->module->name . 'OrderStatus';
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->module->name . '&save' . $this->module->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];
        // Load current values
        $helper->tpl_vars = array(
            'fields_value' => $this->getInitialOrderStatusMapValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm($fieldsForm);
    }

    /**
     * @return string
     */
    public function renderCouponsGenerator()
    {
        $fieldsForm = [];

        $fieldsForm[]['form'] = array(
            'legend' => [
                'title' => $this->l('Coupons'),
            ],
            'description' => sprintf(
                $this->l('This tool allows you to generate unique coupons based on an existing PrestaShop cart rule and then export them in CSV format to your Klaviyo account. To use coupons in Klaviyo, you can follow %sour guide%s.'),
                '<a href="https://help.klaviyo.com/hc/en-us/articles/115005084727-Getting-started-with-coupon-codes-in-Klaviyo#prepare-your-list-of-coupon-codes" target="_blank">',
                '</a>'
            ),
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Cart rule code'),
                    'name' => 'couponCode',
                    'size' => 255,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Quantity to generate'),
                    'name' => 'couponQuantity',
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Download coupons'),
                'class' => 'btn btn-default pull-right',
                'icon' => 'process-icon-download',
            ],
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->module->name;

        // Language
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = $this->context->language->id;

        // Title and toolbar
        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->module->name . 'CouponsGenerator';

        // Load current values
        $helper->tpl_vars = array(
            'fields_value' => [
                'couponCode' => '',
                'couponQuantity' => '',
            ],
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm($fieldsForm);
    }

    /**
     * Check "Coupons" form validation and duplicate the desired cart rule by the number of quantity entered
     * Then a CSV file is generated to import it into Klaviyo
     *
     * @return void
     */
    private function validateAndGenerateCoupons()
    {
        try {
            /** @var CouponGeneratorService $couponGeneratorService */
            $couponGeneratorService = $this->module->getService('klaviyops.klaviyo_service.coupon_generator');
            /** @var CsvService $csvService */
            $csvService = $this->module->getService('klaviyops.util_services.csv');

            $code = Tools::getValue('couponCode', '');
            $quantity = (int)Tools::getValue('couponQuantity', 0);

            if (empty($code)) {
                throw new KlaviyoException($this->l(
                    'The coupon code is empty'
                ));
            }

            if ($quantity <= 0) {
                throw new KlaviyoException($this->l(
                    'The number of coupons must be higher than 0'
                ));
            }

            if ($quantity >= KlaviyoValue::LIMIT_COUPON) {
                throw new KlaviyoException(sprintf(
                    $this->l('The number of coupons must be lower than %s'),
                    KlaviyoValue::LIMIT_COUPON
                ));
            }

            $idCartRule = CartRule::getIdByCode($code);

            if ($idCartRule === false || $idCartRule === null) {
                throw new KlaviyoException($this->l(
                    'This cart rule was not found, please create the rule before generating coupons'
                ));
            }

            $originalCartRule = new CartRule((int)$idCartRule);
            $generatedCartRules = $couponGeneratorService->generateFrom(
                $originalCartRule,
                $quantity
            );

            $data = $couponGeneratorService->buildPayload($generatedCartRules);
            $data = $csvService->csvSerialize($data, [
                'enclosure' => '',
            ]);

            $time = date('YmdHi');
            $filename = "coupon_{$time}.csv";

            header('Content-type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Content-disposition: attachment; filename="' . $filename . '"');

            if (version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
                $this->ajaxRender($data);
                exit;
            } else {
                $this->ajaxDie($data);
            }
        } catch (KlaviyoException $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * Validate configuration form values and save them if there are no errors during validation.
     *
     * @param string $validationMethod
     * @param string $saveMethod
     * @param array $values
     */
    private function validateAndSaveConfig($validationMethod, $saveMethod, $values)
    {
        $config_form_errors = call_user_func(array($this, $validationMethod), $values);
        if ($config_form_errors) {
            $this->errors[] = $this->l(implode(' ', $config_form_errors));
        } else {
            call_user_func(array($this, $saveMethod), $values);
            $this->confirmations[] = $this->l('Settings updated');
        }
    }

    /**
     * Validate Klaviyo config form values.
     *
     * @param array $values Values in submitted form.
     *
     * @return array Error messages if config values aren't valid.
     */
    private function validateConfigValues($values)
    {
        $public_key = trim($values['KLAVIYO_PUBLIC_API']);
        $private_key = trim($values['KLAVIYO_PRIVATE_API']);
        $err = [];

        if ($public_key && !preg_match(self::PUBLIC_KEY_VALIDATION_REGEX, $public_key)) {
            array_push($err, 'Invalid Public API Key.');
        }

        if ($private_key && !preg_match(self::PRIVATE_KEY_VALIDATION_REGEX, $private_key)) {
            array_push($err, 'Invalid Private API Key.');
        }

        if (!$public_key || !$private_key) {
            array_push($err, 'Public and Private API keys are required.');
        }

        return $err;
    }

    /**
     * Save Klaviyo-specific configuration values.
     *
     * @param array $values Values in submitted form.
     */
    private function saveFormKlaviyoValues($values)
    {
        foreach ($values as $key => $value) {
            if (in_array($key, KlaviyoPsModule::CONFIG_KEYS)) {
                Configuration::updateValue($key, trim($value));
            }
        }
        // Ensure KLAVIYO_SUBSCRIBER_LIST is null if KLAVIYO_IS_SYNCING_SUBSCRIBERS is false.
        if (!$values['KLAVIYO_IS_SYNCING_SUBSCRIBERS'] && array_key_exists('KLAVIYO_SUBSCRIBER_LIST', $values)) {
            Configuration::updateValue('KLAVIYO_SUBSCRIBER_LIST', null);
        }

        if (!isset($values['KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE'])) {
            Configuration::updateValue('KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE', 0);
        }
    }

    /**
     * Check if an order status was selected in more than one event type. This will extract only input fields from all
     * the submission keys/values. Then merge all of the values (selected status IDs) into an indexed array to check
     * for duplicates.
     *
     * @param $values
     *
     * @return array Error messages if config values aren't valid.
     */
    private function validateOrderStatusMapValues($values)
    {
        $statusMapValues = array_intersect_key($values, OrderPayloadService::ORDER_STATUS_MAP_DEFAULT);
        $allSelectedOrderStatuses = call_user_func_array('array_merge', array_values($statusMapValues));

        $err = array();
        if (count(array_unique($allSelectedOrderStatuses)) < count($allSelectedOrderStatuses)) {
            $err[] = 'Cannot select the same order status in multiple event maps.';
        }

        return $err;
    }

    /**
     * Save order status map values to configuration table. Combine these inputs into a single associative array.
     *
     * @param $values
     */
    private function saveFormOrderStatusMapValues($values)
    {
        $statusMap = array();
        foreach ($values as $key => $value) {
            if (in_array($key, array_keys(OrderPayloadService::ORDER_STATUS_MAP_DEFAULT))) {
                $statusMap[$key] = $value;
            }
        }

        Configuration::updateValue('KLAVIYO_ORDER_STATUS_MAP', json_encode($statusMap));
    }

    /**
     * Get existing order status map values and format for form. The multi-select option requires an array where
     * the keys correspond to the input name and has '[]' appended e.g.
     *     array(
     *         'klaviyops-statuses-placed[]' => array("3", "4", "10")
     *     )
     *
     * Default values will be populated in the form if mapping hasn't been set for the selected store scope.
     *
     * @return array
     */
    private function getInitialOrderStatusMapValues()
    {
        $jsonMap = $this->module->getConfigurationValueOrNull('KLAVIYO_ORDER_STATUS_MAP');
        $statusMap = json_decode($jsonMap, true);

        $initialValues = array();
        foreach (OrderPayloadService::ORDER_STATUS_MAP_DEFAULT as $field => $value) {
            $initialValues[$field . '[]'] = isset($statusMap[$field]) ? $statusMap[$field] : $value;
        }

        return $initialValues;
    }

    /**
     * Klaviyo uses the 'actionNewsletterRegistrationAfter' hook to subscribe customers to the selected list when a
     * user adds their email address to the PrestaShop Newsletter Subscription module form. This hook was only added
     * in version 2.6.0 so check to ensure compatibility and module is enabled.
     *
     * @return bool
     */
    private function isPsEmailsubscriptionCompatible()
    {
        $emailsubs_module = Module::getInstanceByName('ps_emailsubscription');
        if ($emailsubs_module && $emailsubs_module->isEnabledForShopContext()) {
            return version_compare($emailsubs_module->version, self::PS_EMAILSUBSCRIPTION_MIN_VERSION, '>=');
        }
        return false;
    }
}
