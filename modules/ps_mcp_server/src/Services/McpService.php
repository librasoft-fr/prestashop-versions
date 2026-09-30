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

namespace PrestaShop\Module\PsMcpServer\Services;

use PrestaShop\Module\PsMcpServer\Http\HttpConstants;
use PrestaShop\Module\PsMcpServer\Server\CustomDiscoverer;
use PrestaShop\Module\PsMcpServer\Server\InMemoryTransport;
use PrestaShop\Module\PsMcpServer\Tracker\Segment;
use PsMcpServerDeps\Http\Discovery\Psr17Factory;
use PsMcpServerDeps\Mcp\Capability\Discovery\CachedDiscoverer;
use PsMcpServerDeps\Mcp\Schema\Icon;
use PsMcpServerDeps\Mcp\Schema\ServerCapabilities as SchemaServerCapabilities;
use PsMcpServerDeps\Mcp\Server;
use PsMcpServerDeps\Mcp\Server\Session\Psr16SessionStore;
use PsMcpServerDeps\Mcp\Server\Transport\StdioTransport;
use PsMcpServerDeps\Monolog\Handler\StreamHandler;
use PsMcpServerDeps\Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpService
{
    private const CACHE_DIR = _PS_MODULE_DIR_ . 'ps_mcp_server/.mcp';
    private const CACHE_SUBDIR = self::CACHE_DIR . '/cache';
    private const PAGINATION_LIMIT = 999;

    private CacheInterface $mcpFeaturesCache;
    private CacheInterface $sessionsCache;
    private Psr16SessionStore $sessionStore;
    private CachedDiscoverer $discoverer;
    private LoggerInterface $logger;
    private Server $server;
    private Segment $segment;

    private McpModulesService $mcpModulesService;
    private McpFeaturesService $mcpFeaturesService;

    private bool $forceRegenCache = false;
    private string $serverVersion;

    private array $modulesPathUri = [];

    public function __construct(
        \Ps_mcp_server $module,
        object $mcpModulesService,
        object $mcpFeaturesService,
    ) {
        $this->serverVersion = $module->version;

        if (!$mcpModulesService instanceof McpModulesService || !$mcpFeaturesService instanceof McpFeaturesService) {
            return;
        }

        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
            $this->forceRegenCache = true;
        }

        $psr16McpFeaturesCache = new FilesystemAdapter('features', 0, self::CACHE_SUBDIR);
        $this->mcpFeaturesCache = new Psr16Cache($psr16McpFeaturesCache);

        $psr16SessionCache = new FilesystemAdapter('sessions', 0, self::CACHE_SUBDIR);
        $this->sessionsCache = new Psr16Cache($psr16SessionCache);
        $this->sessionStore = new Psr16SessionStore(
            cache: $this->sessionsCache,
            prefix: 'mcp_sess_',
            ttl: 3600
        );

        $monoLogger = new Logger('mcp');
        $this->mcpModulesService = $mcpModulesService;
        $this->mcpFeaturesService = $mcpFeaturesService;

        $context = \Context::getContext();
        if ($context === null) {
            throw new \PrestaShopException('Context is not defined');
        }

        $this->segment = new Segment($context);

        if (\Configuration::get('PS_MCP_SERVER_LOGS_ENABLED')) {
            $monoLogger->pushHandler(
                new StreamHandler(
                    _PS_MODULE_DIR_ . 'ps_mcp_server/.mcp/.logs',
                    Logger::DEBUG
                )
            );
        }

        $this->logger = $monoLogger;

        $customDiscoverer = new CustomDiscoverer(
            $this->logger,
            $this->mcpFeaturesService,
            $this->segment,
            null,
            null
        );
        $this->discoverer = new CachedDiscoverer($customDiscoverer, $this->mcpFeaturesCache, $this->logger);
    }

    public function executeHttpMcpRequest(): void
    {
        header(HttpConstants::JSON_CONTENT_TYPE_HEADER);

        if ((bool) \Configuration::get('PS_MCP_SERVER_FEATURES_NEED_DISCOVER')) {
            $this->discover();
        }

        if ((bool) \Configuration::get('PS_MCP_SERVER_STARTED') === false) {
            http_response_code(Response::HTTP_SERVICE_UNAVAILABLE);
            echo json_encode(['error' => 'MCP server is not running']);
            exit;
        }

        $psr17Factory = new Psr17Factory();
        $request = $psr17Factory->createServerRequestFromGlobals();
        $this->buildServer('PrestaShop HTTP Server');
        $this->server->run(new InMemoryTransport($request, $this->logger, $this->sessionStore));
    }

    public function runStdioServer(): void
    {
        if ((bool) \Configuration::get('PS_MCP_SERVER_STARTED') === false) {
            $input = fgets(STDIN);
            if ($input) {
                $request = json_decode($input, true);
                $errorResponse = [
                    'jsonrpc' => '2.0',
                    'id' => $request['id'] ?? null,
                    'error' => [
                        'code' => -32002,
                        'message' => 'MCP Server is disabled',
                    ],
                ];
                fwrite(STDOUT, json_encode($errorResponse) . "\n");
                fflush(STDOUT);
            }
            while (!feof(STDIN)) {
                fgets(STDIN);
            }

            return;
        }

        $this->loadPrestaShopContext();
        $this->buildServer('PrestaShop STDIO Server');
        $this->server->run(new StdioTransport());
    }

    public function storeNewModuleRegistered(int $moduleId): void
    {
        $this->mcpModulesService->registerModule($moduleId);
        \Configuration::updateValue('PS_MCP_SERVER_FEATURES_NEED_DISCOVER', true);
        $this->sendNotificationAllChange();
    }

    public function removeModuleRegistered(int $moduleId): void
    {
        $module = $this->mcpModulesService->getModuleById($moduleId);

        if ($module) {
            $this->mcpModulesService->deleteModuleById($moduleId);

            $this->mcpFeaturesService->deleteAllModuleFeatures($moduleId);

            \Configuration::updateValue('PS_MCP_SERVER_FEATURES_NEED_DISCOVER', true);
            $this->sendNotificationAllChange();
        }
    }

    public function fetchAllModulesCompliantWithMcp(): void
    {
        $modulesInDb = $this->mcpModulesService->getAllModules();
        $modulesInDbIds = array_map(fn ($m) => (int) $m['module_id'], $modulesInDb);

        $modulesInstalled = \Module::getModulesInstalled();
        $modulesInstalledIds = [];

        foreach ($modulesInstalled as $moduleInfos) {
            $module = \Module::getInstanceByName($moduleInfos['name']);
            if (!$module instanceof \Module) {
                continue;
            }

            if (method_exists($module, 'isMcpCompliant') && $module->isMcpCompliant()) {
                $modulesInstalledIds[] = (int) $module->id;

                if (!in_array((int) $module->id, $modulesInDbIds)) {
                    $this->storeNewModuleRegistered((int) $module->id);
                    $this->segment->trackMessage('Module Using Mcp', [
                        'module_name' => $module->name,
                        'module_version' => $module->version,
                    ]);
                    $this->logger->info('New module registered for MCP');
                }
            }
        }

        $modulesToUnregister = array_diff($modulesInDbIds, $modulesInstalledIds);
        foreach ($modulesToUnregister as $moduleId) {
            $this->removeModuleRegistered($moduleId);
            $this->logger->info('Module with ID ' . $moduleId . ' uninstalled and unregistered from MCP');
        }

        \Configuration::updateValue('PS_MCP_SERVER_FEATURES_NEED_DISCOVER', false);
        $this->sendNotificationAllChange();
    }

    public function discover(): void
    {
        $this->logger->info('New discovery started');
        $this->discoverer->clearCache();

        if (!isset($this->server)) {
            $this->buildServer('Temporary Discovery Server');
        }

        \Configuration::updateValue('PS_MCP_SERVER_FEATURES_NEED_DISCOVER', false);
    }

    public function isToolReadOnly(string $toolName): bool
    {
        $state = $this->discoverer->discover(_PS_CORE_DIR_, $this->getModulesPathUri(), []);
        $tools = $state->getTools();

        if (isset($tools[$toolName])) {
            $annotations = $tools[$toolName]->tool->annotations;

            return $annotations !== null && $annotations->readOnlyHint === true;
        }

        return false;
    }

    private function getModulesPathUri(): array
    {
        if (!empty($this->modulesPathUri)) {
            return $this->modulesPathUri;
        }

        $modulesRegistered = $this->mcpModulesService->getAllModules();

        foreach ($modulesRegistered as $moduleRegistered) {
            $moduleInstance = \Module::getInstanceById($moduleRegistered['module_id']);
            if ($moduleInstance) {
                $this->modulesPathUri[] = $moduleInstance->getLocalPath() . 'src';
            }
        }

        return $this->modulesPathUri;
    }

    private function buildServer(string $name): void
    {
        $modulesPathUri = $this->getModulesPathUri();

        $iconUrl = \Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'modules/ps_mcp_server/logo.png';

        $serverBuilder = Server::builder()
            ->setServerInfo($name, $this->serverVersion, 'PrestaShop MCP Server', [new Icon(src: $iconUrl)])
            ->setCapabilities(new SchemaServerCapabilities(
                resources: true,
                resourcesSubscribe: false,
                resourcesListChanged: true,
                prompts: true,
                promptsListChanged: true,
                tools: true,
                toolsListChanged: true,
                logging: false,
                completions: true,
            ))
            ->setSession($this->sessionStore)
            ->setDiscovery(_PS_CORE_DIR_, $modulesPathUri, [], $this->mcpFeaturesCache)
            ->setDiscoverer($this->discoverer)
            ->setPaginationLimit(self::PAGINATION_LIMIT)
            ->setLogger($this->logger);

        $this->server = $serverBuilder->build();

        if ($this->forceRegenCache || (bool) \Configuration::get('PS_MCP_SERVER_HOT_CACHING_ENABLED')) {
            $this->logger->info('Cache regenerated');
            $this->discover();
        }
    }

    private function sendNotificationAllChange(): void
    {
        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_TOOLS_CHANGED', true);
        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_PROMPTS_CHANGED', true);
        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCES_CHANGED', true);
        \Configuration::updateValue('PS_MCP_SERVER_NEED_NOTIFY_RESOURCE_TEMPLATES_CHANGED', true);
    }

    private function loadPrestaShopContext(): void
    {
        $context = \Context::getContext();
        if (!$context) {
            throw new \PrestaShopException('Context is not defined');
        }

        foreach (['language' => \Language::class, 'currency' => \Currency::class, 'country' => \Country::class] as $key => $class) {
            if (!$context->$key || !\Validate::isLoadedObject($context->$key)) {
                $confKey = 'PS_' . strtoupper($key) . '_DEFAULT';
                $context->$key = new $class((int) \Configuration::get($confKey));
            }
        }
        if (!$context->shop || !\Validate::isLoadedObject($context->shop)) {
            $context->shop = new \Shop();
        }

        $email = 'mcp@localhost.local';
        $employee = new \Employee();
        if (!$employee::employeeExists($email)) {
            $employee->email = $email;
            $employee->passwd = \Tools::hash(bin2hex(random_bytes(32)));
            $employee->firstname = 'MCP';
            $employee->lastname = 'Server';
            $employee->id_profile = 1;
            $employee->active = true;
            $employee->id_lang = (int) \Configuration::get('PS_LANG_DEFAULT');
            $employee->add();
        } else {
            $employee->getByEmail($email);
        }

        if (\Validate::isLoadedObject($employee)) {
            $context->employee = $employee;
            $this->logger->info('MCP employee loaded', ['id' => $employee->id]);
        }
    }
}
