<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits\Hooks;

trait UseDisplayBackOfficeHeader
{
    use \PrestaShop\Module\PsAskAi\Traits\UseConstants;
    use \PrestaShop\Module\PsAskAi\Traits\UseInitAccountsService;
    use \PrestaShop\Module\PsAskAi\Traits\UseGetService;

    private function isMcpToolsEnabled(): bool
    {
        $moduleManagerBuilder = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
        if ($moduleManagerBuilder === null) {
            return false;
        }

        $moduleManager = $moduleManagerBuilder->build();

        return $moduleManager->isInstalled('ps_mcp_tools') && $moduleManager->isEnabled('ps_mcp_tools');
    }

    /**
     * Whether ps_mbo is usable — the module recommendation install flow rides
     * on its authenticated download path (PE-1741). The drawer uses this flag
     * to gate install CTAs on recommendation cards.
     */
    private function isMboEnabled(): bool
    {
        $moduleManagerBuilder = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
        if ($moduleManagerBuilder === null) {
            return false;
        }

        $moduleManager = $moduleManagerBuilder->build();

        return $moduleManager->isInstalled('ps_mbo') && $moduleManager->isEnabled('ps_mbo');
    }

    private function getMcpServerUrl(): ?string
    {
        $mcpServerService = $this->getService(\PrestaShop\Module\PsAskAi\Service\McpServerService::class);

        return $mcpServerService?->getMcpServerUrl();
    }

    /**
     * @return string
     */
    private function buildAdminUrl(string $routeName): string
    {
        $router = $this->get('router');

        if (!$router) {
            return '';
        }

        return $router->generate($routeName);
    }

    public function bootUseDisplayBackOfficeHeader(): void
    {
    }

    /**
     * @return array{available: bool, currentVersion: ?string, targetVersion: ?string, installUrl: ?string}
     */
    private function getUpdateStatus(): array
    {
        $service = $this->getService(\PrestaShop\Module\PsAskAi\Service\UpdateAvailabilityService::class);

        return $service?->getUpdateStatus()
            ?? ['available' => false, 'currentVersion' => null, 'targetVersion' => null, 'installUrl' => null];
    }

    /**
     * Returns 'no_llm' when no provider+key is configured, 'no_webservices'
     * when PS webservices are off, or 'valid' when the drawer can be used.
     */
    private function getConfigStatus(): string
    {
        if ($this->psAccountsService !== null && !(bool) $this->psAccountsService->isAccountLinked()) {
            return 'no_llm';
        }

        if (!\Configuration::get('PS_WEBSERVICE')) {
            return 'no_webservices';
        }

        if (!(bool) \Configuration::get('PS_SHOP_ENABLE')) {
            return 'maintenance';
        }

        $activeProvider = (string) \Configuration::get(\PrestaShop\Module\PsAskAi\Config\PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
        if ($activeProvider === '' || !in_array($activeProvider, \PrestaShop\Module\PsAskAi\Config\PsAskAiConfig::SUPPORTED_PROVIDERS, true)) {
            return 'no_llm';
        }

        $dbKey = (string) \Configuration::get(\PrestaShop\Module\PsAskAi\Config\PsAskAiConfig::CONFIG_API_KEY);
        if ($dbKey === '') {
            $envVar = \PrestaShop\Module\PsAskAi\Config\PsAskAiConfig::PROVIDER_API_KEY_ENV[$activeProvider];
            $hasEnvKey = getenv($envVar) !== false && getenv($envVar) !== '';

            if (!$hasEnvKey) {
                return 'no_llm';
            }
        }

        if ((bool) \Configuration::get(\PrestaShop\Module\PsAskAi\Config\PsAskAiConfig::CONFIG_API_KEY_ERROR)) {
            return 'invalid_api_key';
        }

        if ((bool) \Configuration::get(\PrestaShop\Module\PsAskAi\Config\PsAskAiConfig::CONFIG_PROVIDER_QUOTA_ERROR)) {
            return 'provider_quota';
        }

        return 'valid';
    }

    private function getBackOfficeHeaderInclusions(): string
    {
        $this->initAccountsService(true);

        $token = $this->psAccountsService?->getOrRefreshToken();
        $isAccountLinked = $this->psAccountsService !== null
            && method_exists($this->psAccountsService, 'isAccountLinked')
            && $this->psAccountsService->isAccountLinked();
        // Built filenames carry the module version (cache-bust); dev-server URLs
        // have no {version} placeholder and pass through untouched.
        $pathVendor = str_replace('{version}', $this->version, $_SERVER['PS_ASK_AI__FRONT_APP_VENDORS_URL']);
        $pathApp = str_replace('{version}', $this->version, $_SERVER['PS_ASK_AI__FRONT_APP_URL']);
        $currentController = \Tools::getValue('controller', '');
        $tabParent = null;
        if ($currentController) {
            $tabId = \Tab::getIdFromClassName($currentController);
            if ($tabId) {
                $langId = \Context::getContext()->language->id;
                $current = new \Tab($tabId);
                // Traverse up until the parent is the root (id_parent <= 0)
                while ($current->id_parent > 0) {
                    $parent = new \Tab($current->id_parent);
                    if ($parent->id_parent <= 0) {
                        break;
                    }
                    $current = $parent;
                }
                $tabParent = $current->name[$langId] ?? null;
            }
        }

        $configureUrl = \Context::getContext()->link->getAdminLink('AdminModules') . '&configure=' . $this->name;

        $updateStatus = $this->getUpdateStatus();

        $jsContext = htmlspecialchars(json_encode([
            'TOKEN' => $token,
            'IS_ACCOUNT_LINKED' => $isAccountLinked,
            'EMPLOYEE_ID' => (int) \Context::getContext()->employee->id,
            'SHOP_ID' => (int) \Context::getContext()->shop->id,
            'PS_VERSION' => _PS_VERSION_,
            'MODULE_VERSION' => $this->version,
            'MCP_SERVER_URL' => $this->getMcpServerUrl(),
            'STREAM_URL' => $this->buildAdminUrl('ps_ask_ai_stream'),
            'STREAM_RESUME_URL' => $this->buildAdminUrl('ps_ask_ai_stream_resume'),
            'STREAM_STOP_URL' => $this->buildAdminUrl('ps_ask_ai_stream_stop'),
            'HISTORY_URL' => $this->buildAdminUrl('ps_ask_ai_history'),
            'CONVERSATIONS_URL' => $this->buildAdminUrl('ps_ask_ai_conversations'),
            'SUGGESTIONS_URL' => $this->buildAdminUrl('ps_ask_ai_suggestions'),
            'MODELS_URL' => $this->buildAdminUrl('ps_ask_ai_models'),
            'CONVERSATION_DELETE_URL' => $this->buildAdminUrl('ps_ask_ai_conversation_delete'),
            'CONVERSATION_TITLE_URL' => $this->buildAdminUrl('ps_ask_ai_conversation_title'),
            'CONVERSATION_ACTIVE_LEAF_URL' => $this->buildAdminUrl('ps_ask_ai_conversation_active_leaf'),
            'MODEL_SELECT_URL' => $this->buildAdminUrl('ps_ask_ai_model_select'),
            'UPLOAD_URL' => $this->buildAdminUrl('ps_ask_ai_upload'),
            'REPORT_ERROR_URL' => $this->buildAdminUrl('ps_ask_ai_report_error'),
            'ENABLE_WEBSERVICE_URL' => $this->buildAdminUrl('ps_ask_ai_enable_webservice'),
            'WEBSERVICE_ENABLED_ONCE' => (bool) \Configuration::get(\PrestaShop\Module\PsAskAi\Config\PsAskAiConfig::CONFIG_WEBSERVICE_ENABLED_ONCE),
            'WEBSERVICE_ADMIN_URL' => \Context::getContext()->link->getAdminLink('AdminWebservice'),
            'MODULE_INSTALL_URL' => $this->buildAdminUrl('ps_ask_ai_module_install'),
            'MODULE_SEARCH_URL' => $this->buildAdminUrl('ps_ask_ai_module_search'),
            'MBO_IS_ENABLED' => $this->isMboEnabled(),
            'MCP_TOOLS_IS_ENABLED' => $this->isMcpToolsEnabled(),
            'TAB_PARENT' => $tabParent,
            'CONFIGURE_URL' => $configureUrl,
            'MODULE_CONFIG_URL' => \Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'ps_ask_ai']),
            'CONFIG_STATUS' => $this->getConfigStatus(),
            'MAINTENANCE_URL' => \Context::getContext()->link->getAdminLink('AdminMaintenance'),
            'MAINTENANCE_DISABLE_URL' => $this->buildAdminUrl('ps_ask_ai_maintenance_disable'),
            'MODULE_MANAGER_URL' => \Context::getContext()->link->getAdminLink('AdminModulesSf'),
            'UPDATE_AVAILABLE' => $updateStatus['available'],
            'UPDATE_CURRENT_VERSION' => $updateStatus['currentVersion'],
            'UPDATE_TARGET_VERSION' => $updateStatus['targetVersion'],
            'UPDATE_INSTALL_URL' => $updateStatus['installUrl'] ?? '',
        ]), ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <script type="module" src="{$pathVendor}" rel="preload"></script>
            <script type="module" src="{$pathApp}" rel="preload"></script>
            <div id="ps-ask-ai-app-context" data-context="$jsContext"></div>

        HTML;
    }

    public function hookDisplayBackOfficeHeader(): string
    {
        return $this->getBackOfficeHeaderInclusions();
    }
}
