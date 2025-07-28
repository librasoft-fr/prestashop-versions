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

    const KLAVIYO_SMS_CONSENT_LABEL_DEFAULT = [
        'en' => 'Subscribe to SMS updates',
        'fr' => 'S\'abonner aux actualités SMS',
        'de' => 'SMS-Updates abonnieren',
        'es' => 'Suscribirse a las actualizaciones de SMS',
        'it' => 'Iscriviti agli aggiornamenti via SMS',
        'ko' => 'SMS 업데이트 구독',
        'br' => 'Inscrever-se em atualizações por SMS'
    ];

    const KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT = [
        'en' => 'By checking this box and entering your phone number above, you consent to receive marketing text messages (e.g. promos, cart reminders) from [Company Name] at the number provided, including messages sent by autodialer. Consent is not a condition of purchase. Msg & data rates may apply. Msg frequency varies. Unsubscribe at any time by replying STOP or clicking the unsubscribe link (where available). Privacy Policy [link] & Terms [link]',
        'fr' => 'En cochant cette case et en saisissant votre numéro de téléphone ci-dessus, vous acceptez de recevoir des SMS marketing (par exemple, des promos, des rappels de panier) de [Nom de l\'entreprise] au numéro fourni, y compris des messages envoyés par composeur automatique. Le consentement n\'est pas une condition d\'achat. Des frais de message et de données peuvent s\'appliquer. La fréquence des messages varie. Vous pouvez vous désabonner à tout moment en répondant STOP ou en cliquant sur le lien de désabonnement (le cas échéant). Politique de confidentialité & Conditions.',
        'de' => 'Wenn du dieses Kästchen aktivierst und deine Telefonnummer eingibst, stimmst du dem Erhalt von Marketing-SMS (z. B. zu Aktionen, Warenkorberinnerungen) von [Unternehmensname] unter der angegebenen Nummer zu. Dies schließt automatisch versendete Nachrichten mit ein. Die Einwilligung ist keine Bedingung für den Kauf. Es können Nachrichten- und Datengebühren anfallen. Die Häufigkeit der Nachrichten variiert. Du kannst dich jederzeit abmelden, indem du mit STOP antwortest oder auf den Abmeldelink klickst (sofern vorhanden). Datenschutzerklärung [Link] und Nutzungsbedingungen [Link]',
        'es' => 'Al marcar esta casilla e incluir tu número de teléfono previamente, aceptas recibir mensajes de texto de marketing (p. ej., promociones y recordatorios de compra) de [nombre de la empresa] en el número facilitado, incluidos los mensajes que se envíen por marcación automática. El consentimiento no es una condición de compra. Pueden aplicarse tarifas por mensajes y datos. La frecuencia de los mensajes no será siempre la misma. Puedes darte de baja en cualquier momento si respondes STOP o haces clic en el enlace para darse de baja (según corresponda). Política de privacidad [enlace] y Términos y condiciones [enlace]',
        'it' => 'Selezionando questa casella e inserendo sopra il tuo numero di telefono, acconsenti a ricevere comunicazioni di marketing via SMS (ad es. promozioni, promemoria per il carrello) da [Nome azienda] al numero fornito, compresi i messaggi inviati tramite strumenti di composizione automatica. Il consenso non è una condizione di acquisto. Potrebbero essere applicati dei costi sui messaggi e sui dati inviati. La frequenza dei messaggi può variare. Puoi annullare l\'iscrizione in qualsiasi momento rispondendo STOP o cliccando sul link di annullamento dell\'iscrizione (se disponibile). Informativa sulla privacy [link] e Termini e condizioni [link]',
        'ko' => '이 확인란을 선택하고 위에 휴대폰 번호를 입력하면 마케팅 문자 메시지 수신에 동의하는 것입니다(예: 자동 다이얼러가 보낸 메시지를 포함하여 제공된 번호로 [회사명]에서 보낸 메시지(프로모션, 장바구니 알림 등)를 수신합니다. 동의는 구매 조건이 아닙니다. 메시지 및 데이터 요금이 적용될 수 있습니다. 메시지 빈도는 달라질 수 있습니다. 언제든지 STOP이라고 답장하거나 구독 취소 링크(사용할 수 있는 경우)를 클릭하여 구독을 취소할 수 있습니다. 개인정보 보호정책 [link] 및 약관 [link]',
        'br' => 'Ao marcar esta caixa e inserir seu número de telefone, você aceita receber mensagens de texto de marketing (como promoções e lembretes de carrinho) da [Nome da empresa] no número fornecido, incluindo mensagens enviadas de forma automática. O consentimento não é uma condição para a compra. Taxas de dados da operadora podem ser aplicadas. A frequência das mensagens varia. Você pode cancelar a inscrição a qualquer momento. Basta responder STOP ou clicar no link para cancelamento de inscrição (caso disponível). Política de privacidade [link] e termos de serviço [link]'
    ];

    /** @var string Minimum version compatible for subscribing profiles using default newsletter form. */
    const PS_EMAILSUBSCRIPTION_MIN_VERSION = '2.6.0';

    /** @var string Minimum version compatible for PrestaShop Account module. The getAccountsCdn method was introduced in 5.2.4. */
    const PS_ACCOUNTS_MIN_VERSION = '5.2.4';

    public function __construct()
    {
        // Bootstrap set to true to utilize default admin controller styling.
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        /** @var EnvService $envService */
        $envService = $this->module->getService('klaviyops.util_services.env');
        $isEditionMode = $envService->get('IS_EDITION_MODE');

        # Load dependencies manager
        $mboInstaller = new \Prestashop\ModuleLibMboInstaller\DependencyBuilder($this->module);

        if ($isEditionMode && !$mboInstaller->areDependenciesMet()) {
            $dependencies = $mboInstaller->handleDependencies();

            $this->context->smarty->assign('dependencies', $dependencies);

            $templatePath = "{$this->module->getLocalPath()}views/templates/admin/dependency_builder.tpl";
            $this->content .= $this->context->smarty->fetch($templatePath);
            parent::initContent();

            return;
        }

        $allValues = Tools::getAllValues();

        // help center links
        $cgiHelpDocUrl = $this->l('https://help.klaviyo.com/hc/en-us/articles/15311862315803');
        $generalHelpDocUrl = $this->l('https://help.klaviyo.com/hc/en-us/articles/360054551492');
        $createAccountUrl = $this->l('https://www.klaviyo.com/signup/prestashop?utm_medium=app-store&utm_source=platform-partner&utm_campaign=');

        if (Tools::isSubmit('submit' . $this->module->name)) {
            $this->validateAndSaveConfig('validateConfigValues', 'saveFormKlaviyoValues', $allValues);
        } elseif (Tools::isSubmit('submit' . $this->module->name . 'OrderStatus')) {
            $this->validateAndSaveConfig('validateOrderStatusMapValues', 'saveFormOrderStatusMapValues', $allValues);
        } elseif (Tools::isSubmit('submit' . $this->module->name . 'CouponsGenerator')) {
            $this->validateAndGenerateCoupons();
        } elseif (Tools::isSubmit('submit' . $this->module->name . 'CouponConfig')) {
            $this->handleCouponConfigForm();
        } elseif (Tools::isSubmit('submit' . $this->module->name . 'BisConfig')) {
            $this->handleBisConfigForm();
        }

        if (!$this->module->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API')) {
            if ($isEditionMode) {
                $createAccountUrl .= KlaviyoValue::UTM_CAMPAIGN_EDITION;
            } else {
                $createAccountUrl .= KlaviyoValue::UTM_CAMPAIGN_PROJECT;
            }

            $this->informations[] = sprintf(
                $this->l('Don\t have a Klaviyo account? %sCreate your account here.%s'),
                sprintf(' <a target="_blank" href="%s">', $createAccountUrl),
                '</a>'
            );
        }

        $this->informations[] = sprintf(
            $this->l('For step-by-step instructions on how to integrate Prestashop with Klaviyo, refer to our %shelp documentation.%s'),
            sprintf(' <a target="_blank" href="%s">', $generalHelpDocUrl),
            '</a>'
        );

        // Prompt to submit order status mapping if default has not been accepted on initial setup.
        if (!$this->module->getConfigurationValueOrNull('KLAVIYO_ORDER_STATUS_MAP')) {
            $this->warnings[] = $this->l('Please complete the order status mapping form or accept the default values.');
        }

        // Display warning if php running as CGI to help avoid auth errors
        if (KlaviyoUtils::shouldWarnCGI()) {
            $this->errors[] = sprintf(
                $this->l('It appears you are running PHP as a CGI. Please follow the %sinstructions here%s to ensure Klaviyo can access your API'),
                sprintf('<a target="_blank" href="%s">', $cgiHelpDocUrl),
                '</a>'
            );
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
            'couponConfig' => $this->renderCouponConfigForm(),
            'bisConfig' => $this->renderBisConfigForm(),
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

            if (!$this->isModuleCompatible('ps_accounts', self::PS_ACCOUNTS_MIN_VERSION)) {
                throw new ModuleVersionException();
            }

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

        // help center link
        $apiKeyHelpDocLink = $this->l('https://help.klaviyo.com/hc/en-us/articles/115005062267#h_01HRFPP8R1AEVQ744SE33FQTEC');
        $smsSubscribeHelpDocLink = $this->l('https://help.klaviyo.com/hc/en-us/articles/4404274419355');

        // Configuration values
        $klaviyoSubscriberList = $this->module->getConfigurationValueOrNull('KLAVIYO_SUBSCRIBER_LIST');
        $klaviyoSmsSubscriberList = $this->module->getConfigurationValueOrNull('KLAVIYO_SMS_SUBSCRIBER_LIST');

        $klaviyoSmsConsentLabelValues = $this->getConfigInMultipleLangs('KLAVIYO_SMS_CONSENT_LABEL');
        $klaviyoSmsConsentDisclosureTextValues = $this->getConfigInMultipleLangs('KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT');

        foreach (Language::getLanguages(false) as $lang) {
            if (empty($klaviyoSmsConsentLabelValues[$lang['id_lang']])) {
                $klaviyoSmsConsentLabelValues[$lang['id_lang']] = (isset(self::KLAVIYO_SMS_CONSENT_LABEL_DEFAULT[$lang['iso_code']]) ?
                    self::KLAVIYO_SMS_CONSENT_LABEL_DEFAULT[$lang['iso_code']] :
                    self::KLAVIYO_SMS_CONSENT_LABEL_DEFAULT['en']
                );
            }
            if (empty($klaviyoSmsConsentDisclosureTextValues[$lang['id_lang']])) {
                $klaviyoSmsConsentDisclosureTextValues[$lang['id_lang']] = (isset(self::KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT[$lang['iso_code']]) ?
                    self::KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT[$lang['iso_code']] :
                    self::KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT['en']
                );
            }
        }

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
                    'desc' => sprintf(
                        $this->l('%sNeed help finding your API keys?%s'),
                        sprintf('<a target="_blank" href="%s">', $apiKeyHelpDocLink),
                        '</a>'
                    )
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
                    'label' => $this->l('Sync PrestaShop email subscribers to Klaviyo'),
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
                [
                    'type' => 'switch',
                    'label' => $this->l('Sync PrestaShop SMS subscribers to Klaviyo'),
                    'name' => 'KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS',
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
                    'desc' => sprintf(
                        $this->l('For the configuration to work SMS must be set up in Klaviyo. %sLearn how to set up SMS%s'),
                        sprintf('<a target="_blank" href="%s">', $smsSubscribeHelpDocLink),
                        '</a>'
                    )
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
            && (
                $this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SUBSCRIBERS')
                || $this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS')
            )
        ) {
            try {
                $api = new KlaviyoApiWrapper();
                $lists = $api->getLists();
            } catch (KlaviyoException $e) {
                $this->errors[] = $this->l(
                    'There was an error accessing lists for account: '
                ) . Configuration::get('KLAVIYO_PUBLIC_API');
            }
        }

        // Ensure we have an array and lists in it.
        if (is_array($lists) && !empty($lists)) {
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

            // Email Subscriber Configuration part
            if ($this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SUBSCRIBERS')) {
                // TODO: We might want to allow someone to reset this value to null.
                // TODO: Provide option whether to subscribe use members or subscribe endpoint.
                // Build form to select Klaviyo Email Subscriber list.
                $fieldsForm[1]['form'] = [
                    'legend' => [
                        'title' => $this->l('Email Subscriber Configuration'),
                    ],
                    'input' => [
                        [
                            'type' => 'select',
                            'label' => $this->l('Email subscriber list'),
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
                    $fieldsForm[1]['form']['warning'] = sprintf(
                        $this->l(
                            'If you want to subscribe profiles to a Klaviyo list using the PrestaShop \'Newsletter Subscription\' module, please make sure the module is enabled and at least version %s or higher.'
                        ),
                        self::PS_EMAILSUBSCRIPTION_MIN_VERSION
                    );
                }
            }

            // SMS Subscriber Configuration part
            if ($this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS')) {
                // Build form to select Klaviyo SNS Subscriber list.
                $fieldsForm[2]['form'] = [
                    'legend' => [
                        'title' => $this->l('SMS Subscriber Configuration'),
                    ]
                ];


                // Check version of ps_emailsubscription module and display notice if incompatible for list subscription.
                if (!is_null($klaviyoSubscriberList) && !is_null($klaviyoSmsSubscriberList) && $klaviyoSmsSubscriberList === $klaviyoSubscriberList) {
                    $fieldsForm[2]['form']['warning'] = $this->l('We recommend using a separate list for SMS subscribers to help better manage campaign and flow SMS message recipients.');
                }

                $fieldsForm[2]['form']['input'] = [
                    [
                        'type' => 'select',
                        'label' => $this->l('SMS subscriber list'),
                        'name' => 'KLAVIYO_SMS_SUBSCRIBER_LIST',
                        'required' => false,
                        'options' => [
                            'query' => $list_arr,
                            'id' => 'id_option',
                            'name' => 'name'
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('When do customers subscribe?'),
                        'name' => 'KLAVIYO_SMS_SUBSCRIBE_TRIGGER',
                        'required' => false,
                        'options' => [
                            'query' => [
                                ['id_option' => 'start_checkout', 'name' => $this->l('Start checkout')],
                                ['id_option' => 'place_order', 'name' => $this->l('Place order')],
                            ],
                            'id' => 'id_option',
                            'name' => 'name'
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Consent label'),
                        'id' => 'kl_sms_consent_label',
                        'name' => 'KLAVIYO_SMS_CONSENT_LABEL',
                        'value' => $klaviyoSmsConsentLabelValues,
                        'required' => true,
                        'lang' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Consent disclosure text'),
                        'id' => 'kl_sms_consent_disclosure_text',
                        'class' => 'kl_tinymce_no_paragraph',
                        'name' => 'KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT',
                        'value' => $klaviyoSmsConsentDisclosureTextValues,
                        'required' => true,
                        'lang' => true,
                        'rows' => 10,
                    ],
                ];
                $fieldsForm[2]['form']['submit'] = [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ];
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
        $helper->languages = $this->context->controller->getLanguages();

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
        $helper->fields_value['KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS'] = $this->module->getConfigurationValueOrNull('KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS');
        $helper->fields_value['KLAVIYO_SMS_SUBSCRIBER_LIST'] = $this->module->getConfigurationValueOrNull('KLAVIYO_SMS_SUBSCRIBER_LIST');
        $helper->fields_value['KLAVIYO_SMS_SUBSCRIBE_TRIGGER'] = $this->module->getConfigurationValueOrNull('KLAVIYO_SMS_SUBSCRIBE_TRIGGER');
        $helper->fields_value['KLAVIYO_SMS_CONSENT_LABEL'] = $klaviyoSmsConsentLabelValues;
        $helper->fields_value['KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT'] = $klaviyoSmsConsentDisclosureTextValues;

        $helper->fields_value['KLAVIYO_LANGUAGE'] = Configuration::get('KLAVIYO_LANGUAGE');
        return $helper->generateForm($fieldsForm);
    }

    public function renderCouponConfigForm()
    {
        $fieldsForm = [];

        $helpDocLink = $this->l('https://help.klaviyo.com/hc/en-us/articles/25151598311195#h_01HYDKX0A9J9WCEY9QCR2C52AF');

        $fieldsForm[]['form'] = array(
            'legend' => [
                'title' => $this->l('Cart Rule Limit'),
            ],
            'description' => sprintf(
                $this->l('This allows you to limit the use of cart rules in your shop. %sLearn more%s'),
                sprintf('<a href="%s" target="_blank">', $helpDocLink),
                '</a>'
            ),
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->l('Limit cart rule usage'),
                    'name' => 'KLAVIYO_COUPON_USAGE_LIMIT_TYPE',
                    'options' => [
                        'query' => [
                            ['id_option' => KlaviyoCouponUsageLimitType::LIMIT_PREFIX, 'name' => $this->l('One cart rule per prefix')],
                            ['id_option' => KlaviyoCouponUsageLimitType::LIMIT_ONE, 'name' => $this->l('One cart rule per order')],
                            ['id_option' => KlaviyoCouponUsageLimitType::LIMIT_NONE, 'name' => $this->l('No limit')],
                        ],
                        'id' => 'id_option',
                        'name' => 'name'
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
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
        $helper->submit_action = 'submit' . $this->module->name . 'CouponConfig';

        // Load current values
        $helper->tpl_vars = array(
            'fields_value' => [
                'KLAVIYO_COUPON_USAGE_LIMIT_TYPE' => $this->module->getConfigurationValueOrNull('KLAVIYO_COUPON_USAGE_LIMIT_TYPE'),
            ],
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm($fieldsForm);
    }

    public function renderBisConfigForm()
    {
        $fieldsForm = [];

        $mailAlertsLink = $this->context->link->getAdminLink('AdminModules') . '&configure=ps_emailalerts';

        // Check if ps_emailalerts module and its BIS notification feature both are enabled.
        $emailAlertsModule = Module::getInstanceByName('ps_emailalerts');
        // MA_CUSTOMER_QTY corresponds to the 'Product availability' toggle in the Mail Alerts configuration page.
        $isEmailAlertsEnabled = Configuration::get('MA_CUSTOMER_QTY') && $emailAlertsModule && $emailAlertsModule->isEnabledForShopContext();
        $warning = !$isEmailAlertsEnabled ? sprintf(
            $this->l('To enable back in stock notifications, turn on product availability in the %smail alerts module%s.'),
            sprintf('<a href="%s">', $mailAlertsLink),
            '</a>'
        ) : null;

        $fieldsForm[]['form'] = array(
            'legend' => [
                'title' => $this->l('Back in Stock'),
            ],
            'warning' => $warning,
            'input' => [
                [
                    'type' => 'switch',
                    'desc' => $this->l('Back in stock emails will no longer send from PrestaShop, and customers will receive them from Klaviyo once your flow is set up.'),
                    'label' => $this->l('Email notification'),
                    'name' => 'KLAVIYO_BIS_ENABLED',
                    'is_bool' => true,
                    'disabled' => !$isEmailAlertsEnabled,
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
        $helper->submit_action = 'submit' . $this->module->name . 'BisConfig';

        // Load current values
        $helper->tpl_vars = array(
            'fields_value' => [
                'KLAVIYO_BIS_ENABLED' => $this->module->getConfigurationValueOrNull('KLAVIYO_BIS_ENABLED'),
            ],
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

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
                'Select one or more order statuses to map to Klaviyo order related events. For example, Klaviyo can record a Fulfilled Order when an order has the status of "Shipped".'
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

        // help center link
        $couponCodeHelpDocLink = $this->l('https://help.klaviyo.com/hc/en-us/articles/115005084727#h_01H9NXQT7NPQ0N7QES86K2ZVZN');

        $fieldsForm[]['form'] = array(
            'legend' => [
                'title' => $this->l('Coupons'),
            ],
            'description' => sprintf(
                $this->l('This tool allows you to generate unique coupons based on an existing PrestaShop cart rule and then export them in CSV format to your Klaviyo account. To use coupons in Klaviyo, you can follow %sour guide%s.'),
                sprintf('<a href="%s" target="_blank">', $couponCodeHelpDocLink),
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

    private function handleCouponConfigForm()
    {
        $configValue = Tools::getValue('KLAVIYO_COUPON_USAGE_LIMIT_TYPE', '');
        Configuration::updateValue('KLAVIYO_COUPON_USAGE_LIMIT_TYPE', $configValue);
    }

    private function handleBisConfigForm()
    {
        $configValue = Tools::getValue('KLAVIYO_BIS_ENABLED', '');
        Configuration::updateValue('KLAVIYO_BIS_ENABLED', $configValue);
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
            array_push($err, $this->l('Invalid Public API Key.'));
        }

        if ($private_key && !preg_match(self::PRIVATE_KEY_VALIDATION_REGEX, $private_key)) {
            array_push($err, $this->l('Invalid Private API Key.'));
        }

        if (!$public_key || !$private_key) {
            array_push($err, $this->l('Public and Private API keys are required.'));
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
        $multilangFleds = [];
        foreach ($values as $key => $value) {
            if (in_array($key, KlaviyoPsModule::CONFIG_KEYS)) {
                Configuration::updateValue($key, trim($value));
            } elseif (strpos($key, 'KLAVIYO_SMS_CONSENT_LABEL_') !== false) {
                $idLang = (int)str_replace('KLAVIYO_SMS_CONSENT_LABEL_', '', $key);
                $multilangFleds['KLAVIYO_SMS_CONSENT_LABEL'][$idLang] = $value;
            } elseif (strpos($key, 'KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT_') !== false) {
                $idLang = (int)str_replace('KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT_', '', $key);
                $multilangFleds['KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT'][$idLang] = $value;
            }
        }

        // Ensure KLAVIYO_SUBSCRIBER_LIST is null if KLAVIYO_IS_SYNCING_SUBSCRIBERS is false.
        if (!$values['KLAVIYO_IS_SYNCING_SUBSCRIBERS'] && array_key_exists('KLAVIYO_SUBSCRIBER_LIST', $values)) {
            Configuration::updateValue('KLAVIYO_SUBSCRIBER_LIST', null);
        }

        // Ensure KLAVIYO_SMS_SUBSCRIBER_LIST is null if KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS is false.
        if (!$values['KLAVIYO_IS_SYNCING_SMS_SUBSCRIBERS'] && array_key_exists('KLAVIYO_SMS_SUBSCRIBER_LIST', $values)) {
            Configuration::updateValue('KLAVIYO_SMS_SUBSCRIBER_LIST', null);
        }

        if (!isset($values['KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE'])) {
            Configuration::updateValue('KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE', 0);
        }

        // Multilang fields
        if (!empty($multilangFleds)) {
            foreach ($multilangFleds as $key => $values) {
                $html = false;
                if ($key === 'KLAVIYO_SMS_CONSENT_DISCLOSURE_TEXT') {
                    $html = true;
                }
                Configuration::updateValue($key, $values, $html);
            }
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
            $err[] = $this->l('Cannot select the same order status in multiple event maps.');
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
        return $this->isModuleCompatible('ps_emailsubscription', self::PS_EMAILSUBSCRIPTION_MIN_VERSION);
    }

    /**
     * Helper method to check if a module is installed and at least a minimum version for compatibility.
     *
     * @param $moduleName
     * @param $minVersion
     * @return bool|int
     */
    private function isModuleCompatible($moduleName, $minVersion)
    {
        $module = Module::getInstanceByName($moduleName);
        if ($module && $module->isEnabledForShopContext()) {
            return version_compare($module->version, $minVersion, '>=');
        }
        return false;
    }

    /**
     * Helper method to support multi-language configuration values across PS 1.7 & 8.
     * Configuration::getInt is the deprecated method, it was deprecated in 1.7.8.0 and
     * removed in version 8.0.0.
     *
     * https://github.com/PrestaShop/PrestaShop/blob/1.7.8.0/classes/Configuration.php#L260-L263
     *
     * @param string $configKey
     * @return mixed
     */
    private function getConfigInMultipleLangs($configKey)
    {
        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
            return Configuration::getConfigInMultipleLangs($configKey);
        }

        return Configuration::getInt($configKey);
    }
}
