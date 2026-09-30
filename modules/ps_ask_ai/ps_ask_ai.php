<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

use PrestaShop\Module\PsAskAi\Config\PsAskAiConfig;
use PrestaShop\Module\PsAskAi\Service\NeuronAiService;
use PrestaShop\Module\PsAskAi\Traits\UseGetService;
use PrestaShop\Module\PsAskAi\Traits\UseHooks;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class ps_ask_ai extends Module
{
    use UseHooks;
    use UseGetService;

    public function __construct()
    {
        $this->name = 'ps_ask_ai';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = 'd40e966db19bdadf526c60a31dda45ca';

        parent::__construct();

        $this->displayName = $this->trans('PrestaShop AskAI', [], 'Modules.Psaskai.Admin');
        $this->description = $this->trans('AI-powered assistant for your PrestaShop store.', [], 'Modules.Psaskai.Admin');

        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];

        $this->bootHooks();
    }

    /**
     * Flags this module as an MCP feature provider.
     *
     * ps_mcp_server's ModuleEventListener calls this on install/enable/upgrade.
     * Returning true registers the module and schedules a discovery pass that
     * scans this module's src/ directory for #[PsMcpTool] annotated methods
     * (see src/Tools/Mcp/*). No further wiring is required.
     */
    public function isMcpCompliant(): bool
    {
        return true;
    }

    public function install()
    {
        $this->log(' *** Starting module installation...');
        try {
            if (
                parent::install()
                && $this->registerHooks()
                && $this->installPsAccounts()
                && $this->installDatabase()
            ) {
                $this->installHooks();
                $this->log(' *** Module successfully installed');

                return true;
            }
        } catch (Throwable $e) {
            $this->reportInstallFailure($e);

            throw $e;
        }

        $this->reportInstallFailure(null);
    }

    /**
     * Report a failed install (table creation, hooks, ps_accounts…) to Sentry.
     * Best-effort: reporting must never turn an install failure into a crash.
     *
     * @param Throwable|null $e null when install failed without throwing
     */
    private function reportInstallFailure(?Throwable $e): void
    {
        try {
            $reporter = new PrestaShop\Module\PsAskAi\Service\SentryReporter();
            if ($e !== null) {
                $reporter->captureException($e, 'module_install_failed');
            } else {
                $reporter->captureWarning('Module install returned false', 'module_install_failed');
            }
        } catch (Throwable $ignored) {
            // Never let error reporting break installation handling.
        }
    }

    public function installDatabase(): bool
    {
        $db = Db::getInstance();
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;

        $statements = [
            "CREATE TABLE IF NOT EXISTS `{$prefix}ps_ask_ai_conversation` (
                `id_conversation` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `thread_id` VARCHAR(64) NOT NULL,
                `id_employee` INT UNSIGNED NOT NULL,
                `id_shop` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) DEFAULT NULL,
                `parent_thread_id` VARCHAR(64) DEFAULT NULL,
                `root_thread_id` VARCHAR(64) NOT NULL,
                `branch_point` INT DEFAULT NULL,
                `active_leaf_thread_id` VARCHAR(64) DEFAULT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                `archived_at` DATETIME DEFAULT NULL,
                UNIQUE KEY `uk_thread_id` (`thread_id`),
                INDEX `idx_employee_shop` (`id_employee`, `id_shop`),
                INDEX `idx_root_thread_id` (`root_thread_id`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `{$prefix}ps_ask_ai_chat_history` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `thread_id` VARCHAR(255) NOT NULL,
                `messages` LONGTEXT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_thread_id` (`thread_id`),
                INDEX `idx_thread_id` (`thread_id`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `{$prefix}ps_ask_ai_tool_log` (
                `id_tool_log` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `id_employee` INT UNSIGNED NOT NULL,
                `id_shop` INT UNSIGNED NOT NULL,
                `thread_id` VARCHAR(64) DEFAULT NULL,
                `tool_call_id` VARCHAR(128) DEFAULT NULL,
                `tool_name` VARCHAR(255) NOT NULL,
                `inputs` TEXT,
                `status` VARCHAR(16) NOT NULL,
                `approval` VARCHAR(16) NOT NULL DEFAULT 'auto',
                `executed_at` DATETIME NOT NULL,
                INDEX `idx_employee_shop` (`id_employee`, `id_shop`),
                INDEX `idx_shop_executed_at` (`id_shop`, `executed_at`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($statements as $sql) {
            if (!$db->execute($sql)) {
                return false;
            }
        }

        return true;
    }

    public function registerHooks(): bool
    {
        return $this->registerHook($this->getHooksNames());
    }

    public function uninstall()
    {
        $this->log(' *** Starting module uninstallation...');
        if (
            parent::uninstall()
            && $this->uninstallHooks()
            && $this->uninstallDatabase()
            && $this->uninstallConfiguration()
        ) {
            $this->log(' *** Module successfully uninstalled');

            return true;
        }

        return false;
    }

    public function uninstallConfiguration(): bool
    {
        $keys = [
            PsAskAiConfig::CONFIG_MODEL_KEY,
            PsAskAiConfig::CONFIG_ACTIVE_PROVIDER,
            PsAskAiConfig::CONFIG_API_KEY,
            PsAskAiConfig::CONFIG_API_KEY_ERROR,
            PsAskAiConfig::CONFIG_PROVIDER_QUOTA_ERROR,
        ];
        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    public function uninstallDatabase(): bool
    {
        $db = Db::getInstance();
        $prefix = _DB_PREFIX_;

        return $db->execute("DROP TABLE IF EXISTS `{$prefix}ps_ask_ai_chat_history`")
            && $db->execute("DROP TABLE IF EXISTS `{$prefix}ps_ask_ai_conversation`")
            && $db->execute("DROP TABLE IF EXISTS `{$prefix}ps_ask_ai_tool_log`");
    }

    public function uninstallHooks(): bool
    {
        $this->log('Uninstalling hooks...');
        foreach ($this->getHooksNames() as $hook) {
            $this->log('Unregistering hook ' . $hook);
            $return = $this->unregisterHook($hook);
            if (!$return) {
                $this->log('Failed to unregister hook ' . $hook);
            } else {
                $this->log('Hook ' . $hook . ' unregistered successfully');
            }
        }
        $this->log('Uninstalling hooks done');

        return true;
    }

    public function getContent()
    {
        $this->context->smarty->assign('module_dir', $this->_path);

        $configFeedback = '';
        $configSuccess = false;
        if (Tools::isSubmit('submitPsAskAiConfig')) {
            $result = $this->postProcessConfig();
            if (strpos($result, 'alert-success') !== false) {
                $_SESSION['ps_ask_ai_config_saved'] = true;
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name
                );

                return '';
            }
            $configFeedback = $result;
        }

        if (!empty($_SESSION['ps_ask_ai_config_saved'])) {
            $configSuccess = true;
            unset($_SESSION['ps_ask_ai_config_saved']);
        }

        $accountsFacade = null;
        $accountsService = null;
        $urlAccountsCdn = '';

        try {
            /** @var PsAccounts $accountsFacade */
            $accountsFacade = $this->getService('ps_ask_ai.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (InstallerException $e) {
            $this->installPsAccounts();
            $accountsFacade = $this->getService('ps_ask_ai.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }

        $isAccountLinked = false;

        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($this->name),
            ]);

            $urlAccountsCdn = $accountsService->getAccountsCdn();
            $isAccountLinked = (bool) $accountsService->isAccountLinked();
        } catch (Throwable $e) {
            $this->context->controller->errors[] = $e->getMessage();
        }

        $currentProvider = (string) Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
        $modelOptions = $this->buildModelOptions($currentProvider);
        $currentModelKey = (string) Configuration::get(PsAskAiConfig::CONFIG_MODEL_KEY);
        $maskedApiKey = $this->buildMaskedApiKey();
        $isConfigured = $currentProvider !== '' && $maskedApiKey !== '';

        $aiApiKeyError = !empty($_GET['ps_ask_ai_key_error']) || (bool) Configuration::get(PsAskAiConfig::CONFIG_API_KEY_ERROR);
        $aiProviderQuotaError = !empty($_GET['ps_ask_ai_quota_error']) || (bool) Configuration::get(PsAskAiConfig::CONFIG_PROVIDER_QUOTA_ERROR);
        $aiApiKeyErrorProvider = '';
        if ($aiApiKeyError || $aiProviderQuotaError) {
            foreach ($this->buildProviderList() as $p) {
                if ($p['id'] === $currentProvider) {
                    $aiApiKeyErrorProvider = $p['label'];
                    break;
                }
            }
        }

        /** @var PrestaShop\Module\PsAskAi\Service\UpdateAvailabilityService|null $updateService */
        $updateService = $this->getService(PrestaShop\Module\PsAskAi\Service\UpdateAvailabilityService::class);
        $updateStatus = $updateService?->getUpdateStatus()
            ?? ['available' => false, 'currentVersion' => null, 'targetVersion' => null, 'installUrl' => null];

        $this->context->smarty->assign([
            'urlAccountsCdn' => $urlAccountsCdn,
            'isAccountLinked' => $isAccountLinked,
            'chatTestUrl' => $this->getChatTestUrl(),
            'testKeyUrl' => $this->getRouteUrl('ps_ask_ai_test_key'),
            'trackUrl' => $this->getRouteUrl('ps_ask_ai_track'),
            'configFeedback' => $configFeedback,
            'aiModelOptions' => $modelOptions,
            'aiCurrentModelKey' => $currentModelKey,
            'aiProviders' => $this->buildProviderList(),
            'aiCurrentProvider' => $currentProvider,
            'aiCurrentApiKeyMasked' => $maskedApiKey,
            'aiIsConfigured' => $isConfigured,
            'aiConfigSuccess' => $isConfigured && $configSuccess,
            'aiApiKeyError' => $aiApiKeyError,
            'aiProviderQuotaError' => $aiProviderQuotaError,
            'aiApiKeyErrorProvider' => $aiApiKeyErrorProvider,
            'psWebserviceEnabled' => (bool) Configuration::get('PS_WEBSERVICE'),
            'psWebserviceEnabledOnce' => (bool) Configuration::get(PsAskAiConfig::CONFIG_WEBSERVICE_ENABLED_ONCE),
            'psWebserviceAdminUrl' => $this->context->link->getAdminLink('AdminWebservice'),
            'psMaintenanceModeEnabled' => !(bool) Configuration::get('PS_SHOP_ENABLE'),
            'psMaintenanceAdminUrl' => $this->context->link->getAdminLink('AdminMaintenance'),
            'aiUpdateAvailable' => $updateStatus['available'],
            'aiUpdateCurrentVersion' => (string) $updateStatus['currentVersion'],
            'aiUpdateTargetVersion' => (string) $updateStatus['targetVersion'],
            'aiUpdateInstallUrl' => (string) ($updateStatus['installUrl'] ?? ''),
            'aiModuleManagerUrl' => $this->context->link->getAdminLink('AdminModulesSf'),
            'maintenanceDisableUrl' => $this->getRouteUrl('ps_ask_ai_maintenance_disable'),
        ]);

        // Event 1: fire only on a plain page render (GET), not while processing
        // a form submit — that POST path already produces Event 3/4/5.
        if (!Tools::isSubmit('submitPsAskAiConfig')) {
            $this->trackSegment(PsAskAiConfig::SEGMENT_EVENT_CONFIG_PAGE_VIEWED, [
                'is_first_config' => !$isConfigured,
            ]);
        }

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Persist the configuration form values. Returns a confirmation or error
     * HTML block ready to render at the top of the configure page.
     *
     * Only one provider+key is stored at a time. Switching provider wipes any
     * previously stored model key so the next chat turn defaults to a model
     * belonging to the new provider.
     */
    private function postProcessConfig(): string
    {
        $provider = trim((string) Tools::getValue('ps_ask_ai_provider', ''));
        $apiKey = trim((string) Tools::getValue('ps_ask_ai_api_key', ''));

        if ($provider === '' || !in_array($provider, PsAskAiConfig::SUPPORTED_PROVIDERS, true)) {
            return $this->displayError($this->trans('Please select a valid provider.', [], 'Modules.Psaskai.Admin'));
        }

        if ($apiKey === '') {
            return $this->displayError($this->trans('Please enter an API key.', [], 'Modules.Psaskai.Admin'));
        }

        try {
            /** @var NeuronAiService $neuron */
            $neuron = $this->getService(NeuronAiService::class);
            $neuron->validateApiKey($provider, $apiKey);
        } catch (Throwable $e) {
            $providerLabel = '';
            foreach ($this->buildProviderList() as $p) {
                if ($p['id'] === $provider) {
                    $providerLabel = $p['label'];
                    break;
                }
            }

            return $this->displayError(
                $this->trans(
                    'We couldn\'t connect to %s with this API key. Check your key or try again later.',
                    [$providerLabel],
                    'Modules.Psaskai.Admin'
                )
            );
        }

        $previousProvider = (string) Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);

        Configuration::updateValue(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER, $provider);
        Configuration::updateValue(PsAskAiConfig::CONFIG_API_KEY, $apiKey);
        Configuration::deleteByName(PsAskAiConfig::CONFIG_API_KEY_ERROR);
        Configuration::deleteByName(PsAskAiConfig::CONFIG_PROVIDER_QUOTA_ERROR);

        if ($previousProvider !== '' && $previousProvider !== $provider) {
            // The stored model key belongs to the old provider's catalog and
            // would 404 against the new one. Clear it so resolveModelAndKey
            // falls back to the new provider's default.
            Configuration::deleteByName(PsAskAiConfig::CONFIG_MODEL_KEY);
        }

        $storedModel = (string) Configuration::get(PsAskAiConfig::CONFIG_MODEL_KEY);
        $model = $storedModel !== '' ? $storedModel : $neuron->getDefaultModelKey($provider);
        $this->trackSegment(PsAskAiConfig::SEGMENT_EVENT_CONFIG_SAVED, [
            'provider' => $provider,
            'model' => $model,
            'is_update' => $previousProvider !== '',
        ]);

        return $this->displayConfirmation($this->trans('Settings updated.', [], 'Modules.Psaskai.Admin'));
    }

    /**
     * Fire-and-forget Segment tracking helper for the configuration page.
     * Resolves the service lazily and swallows any failure so tracking can
     * never break the config flow.
     *
     * @param array<string, mixed> $properties
     */
    private function trackSegment(string $event, array $properties = []): void
    {
        try {
            /** @var PrestaShop\Module\PsAskAi\Service\SegmentService $segment */
            $segment = $this->getService(PrestaShop\Module\PsAskAi\Service\SegmentService::class);
            $segment->track($event, $properties);
        } catch (Throwable $e) {
            // Tracking is best-effort; never surface to the merchant.
        }
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    private function buildProviderList(): array
    {
        return [
            ['id' => 'gemini',    'label' => 'Google (Gemini)'],
            ['id' => 'anthropic', 'label' => 'Anthropic (Claude)'],
            ['id' => 'openai',    'label' => 'OpenAI (ChatGPT)'],
            ['id' => 'mistral',   'label' => 'Mistral'],
        ];
    }

    private function buildMaskedApiKey(): string
    {
        $key = (string) Configuration::get(PsAskAiConfig::CONFIG_API_KEY);
        if ($key === '') {
            return '';
        }

        return str_repeat('•', 18) . mb_substr($key, -3);
    }

    /**
     * Build the model dropdown options. When a provider is active we show
     * only its models — the user cannot select a model belonging to another
     * provider since their key wouldn't authenticate. With no active provider,
     * we show nothing (the form is in the "pick a provider" state).
     *
     * @return array<int, array{key: string, label: string, provider: string}>
     */
    private function buildModelOptions(string $activeProvider): array
    {
        if ($activeProvider === '') {
            return [];
        }

        $options = [];
        try {
            /** @var NeuronAiService $neuron */
            $neuron = $this->getService(NeuronAiService::class);
            foreach ($neuron->getAvailableModels($activeProvider) as $key => $config) {
                $options[] = [
                    'key' => $key,
                    'label' => $config['label'],
                    'provider' => $config['provider'],
                ];
            }
        } catch (Throwable $e) {
            $this->context->controller->errors[] = $e->getMessage();
        }

        return $options;
    }

    private function installPsAccounts(): bool
    {
        try {
            $installer = $this->getService('ps_ask_ai.ps_accounts_installer');
            if ($installer) {
                return $installer->install();
            }
        } catch (Throwable $e) {
            $this->context->controller->errors[] = $e->getMessage();
        }

        return true;
    }

    /**
     * Build the BO URL for the SSE chat-test demo page. Returns null if the
     * Symfony router can't be reached (e.g. router cache not yet warmed).
     */
    private function getChatTestUrl(): ?string
    {
        return $this->getRouteUrl('ps_ask_ai_chat_test');
    }

    /**
     * Generate a BO URL for a named Symfony route. Returns null when the
     * router is unavailable (e.g. cache not warmed on first install).
     */
    private function getRouteUrl(string $routeName): ?string
    {
        try {
            $container = SymfonyContainer::getInstance();
            if ($container === null) {
                return null;
            }

            return $container->get('router')->generate($routeName);
        } catch (Throwable $e) {
            return null;
        }
    }
}
