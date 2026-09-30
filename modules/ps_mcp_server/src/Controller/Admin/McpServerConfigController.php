<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpServer\Controller\Admin;

use PrestaShop\Module\PsMcpServer\Exceptions\ContextException;
use PrestaShop\Module\PsMcpServer\Helper\ModuleHelper;
use PrestaShop\Module\PsMcpServer\Http\CloudSyncClient;
use PrestaShop\Module\PsMcpServer\Services\McpAllowedUsersService;
use PrestaShop\Module\PsMcpServer\Services\McpFeaturesService;
use PrestaShop\Module\PsMcpServer\Services\McpService;
use PrestaShop\Module\PsMcpServer\Tracker\Segment;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpServerConfigController extends FrameworkBundleAdminController
{
    private \Ps_mcp_server $module;

    public function __construct()
    {
        $module = \Module::getInstanceByName('ps_mcp_server');

        $this->module = $module;

        if ((bool) \Configuration::get('PS_MCP_SERVER_FIRST_DISCOVERY_DONE') && (bool) \Configuration::get('PS_MCP_SERVER_FEATURES_NEED_DISCOVER')
            || (bool) \Configuration::get('PS_MCP_SERVER_HOT_CACHING_ENABLED')
        ) {
            $mcpService = $this->module->getService(McpService::class);
            $mcpService->discover();
        }
    }

    #[AdminSecurity("is_granted('read', 'AdminModulesSf')")]
    public function index(Request $request): Response
    {
        $this->handleFirstDiscovery();

        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();

        if ($moduleManagerBuilder == null) {
            throw new \PrestaShopException('ModuleManagerBuilder is not defined');
        }

        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager == null) {
            throw new \PrestaShopException('ModuleManager is not defined');
        }

        $context = \Context::getContext();

        if ($context == null) {
            throw new ContextException();
        }

        $shopContext = $context->shop;

        if ($shopContext == null) {
            throw new ContextException();
        }

        $employee = $context->employee;

        if ($employee == null) {
            throw new \PrestaShopException('Employee is not defined');
        }

        $accountsModule = \Module::getInstanceByName('ps_accounts');

        $this->addDataForVueApp($context);

        $assets = $this->getAssets($context);

        return $this->render('@Modules/ps_mcp_server/views/templates/admin/base.html.twig', [
            'layoutTitle' => 'MCP Configuration page',
            'LIVE_MODE_VUEJS' => (bool) \Ps_mcp_server::getConfig()['LIVE_MODE_VUEJS'],
            'ps_account_cdn_url' => $accountsModule ? $accountsModule->getParameter('ps_accounts.accounts_cdn_url') : 'https://assets.prestashop3.com/accounts-components/1/psaccountsVue.js',
            'mcp_js_url' => $assets['js'],
            'mcp_css_url' => $assets['css'],
            'mcp_preload_urls' => $assets['preload'],
        ]);
    }

    private function getAssets(\Context $context): array
    {
        $moduleName = 'ps_mcp_server';

        $shopContext = $context->shop;

        if ($shopContext == null) {
            throw new ContextException();
        }

        $publicBase = rtrim((string) $shopContext->getBaseURL(true), '/') . '/modules/' . $moduleName . '/views/';

        $viewPath = _PS_MODULE_DIR_ . $moduleName . '/views';
        $manifestFilePath = $viewPath . '/.vite/manifest.json';
        $manifestContent = file_get_contents($manifestFilePath);

        if (!is_string($manifestContent)) {
            throw new \PrestaShopException('manifest file is not readable');
        }

        $decodedJson = json_decode($manifestContent, true);
        $manifest = $decodedJson ?? [];

        $entryKey = 'js/main.js';
        $cssKey = 'style.css';

        $mcpJsUrl = $publicBase . ltrim($manifest[$entryKey]['file'] ?? '/js/main.js', '/');
        $mcpCssUrl = $publicBase . ltrim($manifest[$cssKey]['file'] ?? '/css/style.css', '/');

        $preloadUrls = [];

        $imports = $manifest[$entryKey]['imports'] ?? [];
        $imports = array_values(array_unique($imports));

        foreach ($imports as $importKey) {
            if (!empty($manifest[$importKey]['file'])) {
                $preloadUrls[] = [
                    'rel' => 'modulepreload',
                    'as' => 'script',
                    'href' => $publicBase . ltrim($manifest[$importKey]['file'], '/'),
                ];
            }
        }

        $language = $context->language;

        $iso = strtolower($language->iso_code ?? 'en');
        $langKey = "translations/{$iso}.json";

        if (!isset($manifest[$langKey]) && $iso !== 'en') {
            $langKey = 'translations/en.json';
        }

        if (!empty($manifest[$langKey]['file'])) {
            $preloadUrls[] = [
                'rel' => 'modulepreload',
                'as' => 'script',
                'href' => $publicBase . ltrim($manifest[$langKey]['file'], '/'),
            ];
        }

        $preloadUrls[] = [
            'rel' => 'preload',
            'as' => 'style',
            'href' => $mcpCssUrl,
        ];

        return [
            'js' => $mcpJsUrl,
            'css' => $mcpCssUrl,
            'preload' => $preloadUrls,
        ];
    }

    private function addDataForVueApp(\Context $context): void
    {
        $moduleHelper = $this->module->getService(ModuleHelper::class);

        $shopContext = $context->shop;

        if ($shopContext == null) {
            throw new ContextException();
        }

        $currentEmployee = $context->employee;

        if ($currentEmployee == null) {
            throw new \PrestaShopException('Employee is not defined');
        }

        $profiles = (array) \ProfileCore::getProfiles(null);
        $shopEmployees = [];

        foreach ($profiles as $profile) {
            $employees = (array) \EmployeeCore::getEmployeesByProfile($profile['id_profile']);

            foreach ($employees as $employee) {
                $shopEmployees[] = $employee;
            }
        }

        $accountsModule = \Module::getInstanceByName('ps_accounts');
        $accountIsOnboarded = false;

        if ($accountsModule && version_compare($accountsModule->version, '8.0.0', '>=')) {
            $accountsService = $accountsModule->getService('PrestaShop\Module\PsAccounts\Service\PsAccountsService');
            $isShopIdentityCreated = $accountsService->isShopIdentityCreated();
            $isShopIdentityVerified = $accountsService->isShopIdentityVerified();
            $isShopPointOfContactSet = $accountsService->isShopPointOfContactSet();

            $accountIsOnboarded = $isShopIdentityCreated && $isShopIdentityVerified && $isShopPointOfContactSet;
        }

        if ($accountIsOnboarded && !(bool) \Configuration::get('PS_MCP_SERVER_ACCOUNTS_ONBOARDING_TRACKED')) {
            $segment = new Segment($context);
            $segment->trackMessage('Accounts Onboarding Completed', ['module_name' => 'ps_mcp_server']);

            \Configuration::updateValue('PS_MCP_SERVER_ACCOUNTS_ONBOARDING_TRACKED', true);
        }

        $mcpAllowedUsersService = $this->module->getService(McpAllowedUsersService::class);

        $mcpFeaturesService = $this->module->getService(McpFeaturesService::class);

        \Media::addJsDef([
            'mcpConfig' => [
                'isoCode' => $context->language !== null ? $context->language->iso_code : 'en',
                'dependenciesState' => [
                    'ps_mbo' => $moduleHelper->buildModuleInformation(
                        'ps_mbo',
                        'PrestaShop Marketplace in your Back Office'
                    ),
                    'ps_eventbus' => $moduleHelper->buildModuleInformation(
                        'ps_eventbus',
                        'PrestaShop EventBus'
                    ),
                    'ps_accounts' => $moduleHelper->buildModuleInformation(
                        'ps_accounts',
                        'PrestaShop Account'
                    ),
                    'ps_mcp_tools' => $moduleHelper->buildModuleInformation(
                        'ps_mcp_tools',
                        'PrestaShop MCP Tools'
                    ),
                ],
                'accountOnboarded' => $accountIsOnboarded,
                'mcpAjaxPath' => $this->generateUrl('ps_mcp_server_ajax'),
                'cloudSyncAlreadyOnboarded' => (new CloudSyncClient())->consentExists(),
                'mcpHotCachingEnabled' => (bool) \Configuration::get('PS_MCP_SERVER_HOT_CACHING_ENABLED'),
                'mcpLogsEnabled' => (bool) \Configuration::get('PS_MCP_SERVER_LOGS_ENABLED'),
                'mcpServerRunning' => (bool) \Configuration::get('PS_MCP_SERVER_STARTED'),
                'mcpUrl' => rtrim((string) $shopContext->getBaseURL(true), '/') . '/mcp',
                'shopEmployees' => $shopEmployees,
                'allowedUsers' => $mcpAllowedUsersService->getAllAllowedUsers(),
                'authDisabled' => (bool) \Configuration::get('PS_MCP_SERVER_AUTH_DISABLED'),
                'allowInsecureMode' => (defined('_PS_MCP_SERVER_ALLOW_INSECURE_MODE_') && constant('_PS_MCP_SERVER_ALLOW_INSECURE_MODE_') === true),
                'modulesFeatures' => $mcpFeaturesService->getAllModuleFeatures(),
                'sentryEnvironment' => \Ps_mcp_server::getConfig()['SENTRY_ENVIRONMENT'],
                'sentryRelease' => 'ps_mcp_server@' . $this->module->version,
                'viteSentryDsn' => \Ps_mcp_server::getConfig()['VITE_SENTRY_DSN'],
            ],
        ]);
    }

    private function handleFirstDiscovery(): void
    {
        $firstConfigDone = (bool) \Configuration::get('PS_MCP_SERVER_FIRST_DISCOVERY_DONE');

        $module = \Module::getInstanceByName('ps_mcp_server');

        if (!$module->tableExist('mcp_server_modules') || !$module->tableExist('mcp_server_features') || !$module->tableExist('mcp_server_allowed_users')) {
            $module->installDatabaseTables();

            $firstConfigDone = false;
        }

        if (!$firstConfigDone) {
            try {
                $context = \Context::getContext();

                if ($context == null) {
                    throw new \PrestaShopException('Context is not defined');
                }

                $link = $context->link;

                if ($link == null) {
                    throw new \PrestaShopException('Context link is not defined');
                }

                $controllerLink = $link->getAdminLink('McpServerConfigController', true, ['route' => 'ps_mcp_server_config']);
                $pos = strpos($controllerLink, '?');
                $controllerUrl = $pos === false ? $controllerLink : substr($controllerLink, 0, $pos);

                \Configuration::updateValue('PS_MCP_SERVER_CONFIGURATION_URL', $controllerUrl);

                $mcpService = $this->module->getService(McpService::class);

                if ($mcpService != null) {
                    $mcpService->fetchAllModulesCompliantWithMcp();
                    $mcpService->discover();

                    \Configuration::updateValue('PS_MCP_SERVER_FIRST_DISCOVERY_DONE', true);
                }
            } catch (\Exception $e) {
                \PsMcpServerDeps\Sentry\captureException($e);
                $this->addFlash('error', 'Error during automatic discovery: ' . $e->getMessage());
            }
        }
    }
}
