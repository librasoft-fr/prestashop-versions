#!/usr/bin/env php
<?php

require_once dirname(__DIR__, 5) . '/config/config.inc.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsMcpServer\Services\McpService;

$mcpModule = Module::getInstanceByName('ps_mcp_server');

$mcpService = $mcpModule->getService(McpService::class);
$mcpService->runStdioServer();
