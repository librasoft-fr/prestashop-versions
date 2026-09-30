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

use PrestaShop\Module\PsMcpServer\Helper\ModuleHelper;
use PrestaShop\Module\PsMcpServer\Http\CloudSyncClient;
use PrestaShop\Module\PsMcpServer\Services\McpAllowedUsersService;
use PrestaShop\Module\PsMcpServer\Services\McpFeaturesService;
use PrestaShop\Module\PsMcpServer\Services\McpService;
use PrestaShop\Module\PsMcpServer\Tracker\Segment;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Domain\Module\Exception\ModuleException;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use PsMcpServerDeps\Prestashop\ModuleLibMboInstaller\Installer as MBOInstaller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpServerAjaxController extends FrameworkBundleAdminController
{
    private const ERR_MISSING_MODULE_NAME = 'Missing moduleName parameter';

    private const ALLOWED_ACTIONS = [
        'installDependency',
        'enableDependency',
        'upgradeDependency',
        'fetchAccountsContext',
        'switchMcpServer',
        'updateMcpFeatures',
        'toggleModuleStatus',
        'saveSettings',
        'getAllowedUsers',
        'addAllowedUser',
        'regenerateUserToken',
        'deleteAllowedUser',
        'changeUserRole',
        'updateAuthDisabled',
        'sendCloudSyncConsent',
    ];

    private \Ps_mcp_server $module;
    private Segment $segment;

    public function __construct()
    {
        $context = \Context::getContext();

        if ($context === null) {
            throw new \PrestaShopException('Context is not defined');
        }

        $this->segment = new Segment($context);

        $module = \Module::getInstanceByName('ps_mcp_server');

        $this->module = $module;
    }

    #[AdminSecurity("is_granted('read', 'AdminModulesSf')")]
    public function ajax(Request $request): JsonResponse
    {
        try {
            $data = json_decode((string) $request->getContent(), true);
            $action = $data['action'] ?? null;

            if (!$action) {
                throw new \PrestaShopException('Missing action parameter');
            }

            if (!in_array($action, self::ALLOWED_ACTIONS, true)) {
                throw new \PrestaShopException('Unknown action: ' . $action);
            }

            $reflection = new \ReflectionMethod($this, $action);

            $parameters = $reflection->getParameters();

            $type = empty($parameters) ? null : $parameters[0]->getType();

            if ($type instanceof \ReflectionNamedType && $type->getName() === Request::class) {
                return $this->$action($request);
            }

            return $this->$action();
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'An internal error occurred. Please try again later.',
            ], 500);
        }
    }

    public function installDependency(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!isset($data['moduleName'])) {
            throw new \InvalidArgumentException(self::ERR_MISSING_MODULE_NAME);
        }

        $technicalName = $data['moduleName'];
        try {
            $wasInstalled = \Module::isInstalled($technicalName);

            if ($technicalName === 'ps_mbo') {
                $mboInstaller = new MBOInstaller(_PS_VERSION_);

                $mboInstaller->installModule();
            } else {
                $this->getModuleManager()->install($technicalName);
            }

            $moduleHelper = $this->module->getService(ModuleHelper::class);

            if (!$wasInstalled) {
                $this->trackModuleInstallation($technicalName);
            }

            return new JsonResponse([
                'status' => 'success',
                'module_info' => $moduleHelper->buildModuleInformation($technicalName),
            ]);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function enableDependency(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!isset($data['moduleName'])) {
            throw new \InvalidArgumentException(self::ERR_MISSING_MODULE_NAME);
        }

        $technicalName = $data['moduleName'];

        try {
            $this->getModuleManager()->enable($technicalName);

            $moduleHelper = $this->module->getService(ModuleHelper::class);

            return new JsonResponse([
                'status' => 'success',
                'module_info' => $moduleHelper->buildModuleInformation($technicalName),
            ]);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function upgradeDependency(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!isset($data['moduleName'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing moduleName parameter'], 400);
        }

        $technicalName = $data['moduleName'];
        $temporaryFile = null;

        $moduleHelper = $this->module->getService(ModuleHelper::class);

        try {
            $temporaryFile = $moduleHelper->downloadModuleFromAddons($technicalName);
            $this->getModuleManager()->upgrade($technicalName, $temporaryFile);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);

            $this->getModuleManager()->upgrade($technicalName);
        } finally {
            if ($temporaryFile !== null && file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }

        return new JsonResponse([
            'status' => 'success',
            'module_info' => $moduleHelper->buildModuleInformation($technicalName),
        ]);
    }

    public function fetchAccountsContext(): JsonResponse
    {
        $mcpModule = \Module::getInstanceByName('ps_mcp_server');

        if (!$mcpModule) {
            throw new \PrestaShopException('PrestaShop MCP not found');
        }

        if ($this->getModuleManager()->isInstalled('ps_accounts') && $this->getModuleManager()->isEnabled('ps_accounts')) {
            $accountsModule = \Module::getInstanceByName('ps_accounts');

            if (!$accountsModule) {
                throw new \PrestaShopException('PrestaShop Accounts not found');
            }

            $accountsPresenter = $accountsModule->getService('PrestaShop\Module\PsAccounts\Presenter\PsAccountsPresenter');

            return new JsonResponse([
                'status' => 'success',
                'context' => $accountsPresenter->present((string) $mcpModule->name),
            ]);
        }

        return new JsonResponse([
            'status' => 'error',
            'context' => null,
        ]);
    }

    public function switchMcpServer(Request $request): JsonResponse
    {
        try {
            $newState = !(bool) \Configuration::get('PS_MCP_SERVER_STARTED');

            \Configuration::updateValue('PS_MCP_SERVER_STARTED', $newState);

            $message = $newState ? 'MCP server started successfully' : 'MCP server stopped successfully';

            return new JsonResponse([
                'status' => 'success',
                'message' => $message,
                'mcp_running' => $newState,
            ]);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMcpFeatures(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);

        $features = $data['features'] ?? null;
        $isActive = $data['isActive'] ?? null;

        if (!is_array($features) || empty($features) || $isActive === null) {
            throw new \PrestaShopException('Missing features array or isActive parameter');
        }

        $mcpFeaturesService = $this->module->getService(McpFeaturesService::class);

        $mcpService = $this->module->getService(McpService::class);

        $mcpFeaturesService->updateFeaturesStatusBatch($features, (bool) $isActive);
        $mcpService->discover();

        $changedTypes = array_unique(array_column($features, 'type'));
        foreach ($changedTypes as $type) {
            switch ($type) {
                case 'tool':
                    \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_TOOLS_CHANGED', true);
                    break;
                case 'prompt':
                    \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_PROMPTS_CHANGED', true);
                    break;
                case 'resource':
                    \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCES_CHANGED', true);
                    break;
                case 'resourceTemplate':
                    \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCE_TEMPLATES_CHANGED', true);
                    break;
                default:
                    break;
            }
        }

        return new JsonResponse(['status' => 'success']);
    }

    public function toggleModuleStatus(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        $moduleId = $data['moduleId'] ?? null;
        $isActive = $data['isActive'] ?? null;

        if ($moduleId === null || $isActive === null) {
            throw new \PrestaShopException('Missing moduleId or isActive parameter');
        }

        $mcpFeaturesService = $this->module->getService(McpFeaturesService::class);

        $mcpService = $this->module->getService(McpService::class);

        $mcpFeaturesService->updateModuleStatus((int) $moduleId, (bool) $isActive);
        $mcpService->discover();

        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_TOOLS_CHANGED', true);
        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_PROMPTS_CHANGED', true);
        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCES_CHANGED', true);
        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCE_TEMPLATES_CHANGED', true);

        return new JsonResponse(['status' => 'success', 'is_active' => (bool) $isActive]);
    }

    public function saveSettings(Request $request): JsonResponse
    {
        try {
            $data = json_decode((string) $request->getContent(), true);

            if (!$data || !isset($data['setting']) || !array_key_exists('value', $data)) {
                $response = ['status' => 'error', 'message' => 'Invalid data'];
                $statusCode = 400;
            } else {
                $setting = $data['setting'];
                $value = $data['value'];

                switch ($setting) {
                    case 'mcp_hot_caching_enabled':
                        \Configuration::updateValue('PS_MCP_SERVER_HOT_CACHING_ENABLED', $value);
                        $response = ['status' => 'success', 'message' => 'Setting saved'];
                        $statusCode = 200;
                        break;

                    case 'mcp_logs_enabled':
                        \Configuration::updateValue('PS_MCP_SERVER_LOGS_ENABLED', (bool) $value);
                        $response = ['status' => 'success', 'message' => 'Setting saved'];
                        $statusCode = 200;
                        break;

                    case 'relaunch_discover_modules_and_items':
                        $mcpService = $this->module->getService(McpService::class);
                        $mcpService->fetchAllModulesCompliantWithMcp();
                        $mcpService->discover();

                        $response = ['status' => 'success', 'message' => 'Module discovery reset successfully'];
                        $statusCode = 200;
                        break;

                    default:
                        $response = ['status' => 'error', 'message' => 'Unknown setting'];
                        $statusCode = 400;
                        break;
                }
            }

            return new JsonResponse($response, $statusCode);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllowedUsers(): JsonResponse
    {
        try {
            $mcpAllowedUsersService = $this->module->getService(McpAllowedUsersService::class);

            $allowedUsers = $mcpAllowedUsersService->getAllAllowedUsers();

            return new JsonResponse([
                'status' => 'success',
                'data' => $allowedUsers,
            ]);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function addAllowedUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode((string) $request->getContent(), true);

            if (!isset($data['email'])) {
                throw new \InvalidArgumentException('Missing parameters');
            }

            $email = trim($data['email']);
            $generateToken = (bool) ($data['generateToken'] ?? true);
            $role = $data['role'];

            if (!\Validate::isEmail($email)) {
                throw new \InvalidArgumentException('Invalid email format');
            }

            return $this->addEmailToMemberList($email, $role, $generateToken);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function regenerateUserToken(Request $request): JsonResponse
    {
        try {
            $data = json_decode((string) $request->getContent(), true);

            if (!isset($data['email'])) {
                throw new \InvalidArgumentException('Missing email');
            }

            $email = trim($data['email']);

            $mcpAllowedUsersService = $this->module->getService(McpAllowedUsersService::class);

            $result = $mcpAllowedUsersService->regenerateTokenForUser($email);

            if (!is_array($result) || !isset($result['token'])) {
                throw new \InvalidArgumentException('Failed to regenerate token');
            }

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Token regenerated successfully',
                'token' => $result['token'],
            ]);
        } catch (\Exception $e) {
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function changeUserRole(Request $request): JsonResponse
    {
        try {
            $data = json_decode((string) $request->getContent(), true);

            if (!isset($data['email']) || !isset($data['role'])) {
                throw new \InvalidArgumentException('Missing parameters');
            }

            $email = trim($data['email']);
            $role = $data['role'];

            if ($role !== 'viewer' && $role !== 'editor') {
                throw new \InvalidArgumentException('Invalid role specified');
            }

            $mcpAllowedUsersService = $this->module->getService(McpAllowedUsersService::class);

            $result = $mcpAllowedUsersService->changeRoleForUser($email, $role);

            if (!is_array($result) || !isset($result['role'])) {
                throw new \InvalidArgumentException('Failed to change user role');
            }

            return new JsonResponse([
                'status' => 'success',
                'message' => 'User role changed successfully',
                'role' => $result['role'],
            ]);
        } catch (\Exception $e) {
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function deleteAllowedUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode((string) $request->getContent(), true);

            if (!isset($data['email'])) {
                throw new \InvalidArgumentException('Missing email');
            }

            $mcpAllowedUsersService = $this->module->getService(McpAllowedUsersService::class);

            $result = $mcpAllowedUsersService->deleteAllowedUser($data['email']);

            if (!$result) {
                throw new \InvalidArgumentException('Failed to remove employee');
            }

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Employee removed from whitelist successfully',
            ]);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function updateAuthDisabled(Request $request): JsonResponse
    {
        try {
            $data = json_decode((string) $request->getContent(), true);
            $authDisabled = (bool) ($data['authDisabled'] ?? false);

            if ($authDisabled) {
                $insecureModeAllowed = (bool) defined('_PS_MCP_SERVER_ALLOW_INSECURE_MODE_') && constant('_PS_MCP_SERVER_ALLOW_INSECURE_MODE_') === true;

                if (!$insecureModeAllowed) {
                    throw new \InvalidArgumentException('Insecure mode is not allowed');
                }
            }

            \Configuration::updateValue('PS_MCP_SERVER_AUTH_DISABLED', $authDisabled);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Authentication setting updated successfully',
            ]);
        } catch (\Exception $e) {
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function sendCloudSyncConsent(): JsonResponse
    {
        try {
            if (!$this->getModuleManager()->isInstalled('ps_accounts') || !$this->getModuleManager()->isEnabled('ps_accounts')) {
                throw new ModuleException('ps_accounts module is not installed or enabled');
            }

            $cloudSyncClient = new CloudSyncClient();

            $result = $cloudSyncClient->sendConsent();

            if ($result['httpCode'] === 201 || $result['httpCode'] === 200) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Consent data sent successfully',
                    'data' => $result['body'],
                ]);
            }

            return new JsonResponse([
                'status' => 'error',
                'message' => 'CloudSync API responded with HTTP code ' . $result['httpCode'],
            ], 500);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'An internal error occurred. Please try again later.',
            ], 500);
        }
    }

    public function addEmailToMemberList(string $email, string $role, bool $generateToken = true): JsonResponse
    {
        try {
            if ($email == null) {
                throw new \InvalidArgumentException('Email cannot be null');
            }

            if ($role !== 'viewer' && $role !== 'editor') {
                throw new \InvalidArgumentException('Invalid role specified');
            }

            $mcpAllowedUsersService = $this->module->getService(McpAllowedUsersService::class);

            $emailExist = $mcpAllowedUsersService->getUserByEmail($email);

            if ($emailExist) {
                $response = [
                    'status' => 'error',
                    'employee_exists' => [
                        'email' => $email,
                    ],
                    'message' => 'Employee already exists in whitelist',
                ];
            } else {
                $result = $mcpAllowedUsersService->addAllowedUser($email, $role, $generateToken);

                if (is_array($result)) {
                    $employeeData = [
                        'email' => $result['email'],
                        'role' => $result['role'],
                        'has_token' => (bool) ($result['has_token'] ?? false),
                        'created_at' => $result['created_at'],
                    ];

                    if (isset($result['token'])) {
                        $employeeData['token'] = $result['token'];
                    }

                    $response = [
                        'status' => 'success',
                        'employee_added' => $employeeData,
                        'message' => 'Employee added to whitelist successfully',
                    ];
                } else {
                    throw new \PrestaShopException('Failed to add user to whitelist');
                }
            }

            return new JsonResponse($response);
        } catch (\Exception $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $statusCode = $e instanceof \InvalidArgumentException ? 400 : 500;

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function getModuleManager(): ModuleManager
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();

        if ($moduleManagerBuilder == null) {
            throw new \PrestaShopException('ModuleManagerBuilder is not defined');
        }

        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager == null) {
            throw new \PrestaShopException('ModuleManager is not defined');
        }

        return $moduleManager;
    }

    public function trackModuleInstallation(string $technicalName): void
    {
        $eventMap = [
            'ps_accounts' => 'Account Installed',
            'ps_eventbus' => 'Eventbus Installed',
            'ps_mbo' => 'MBO Installed',
            'ps_mcp_tools' => 'MCP Tools Installed',
        ];

        if (!isset($eventMap[$technicalName])) {
            return;
        }

        $eventName = $eventMap[$technicalName];
        $this->segment->trackMessage($eventName, ['installation_source' => 'ps_mcp_server', 'module_name' => 'ps_mcp_server']);
    }
}
