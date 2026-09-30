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

namespace PrestaShop\Module\PsMcpServer\Server;

use PrestaShop\Module\PsMcpServer\Services\McpAllowedUsersService;
use Psr\Log\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TransportAuthenticator
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function tryOAuthAuth(string $jwt, McpAllowedUsersService $mcpAllowedUsersService): ?string
    {
        try {
            $accountModule = \Module::getInstanceByName('ps_accounts');
            $context = \Context::getContext();
            $shopContext = $context ? $context->shop : null;

            $validatorService = $accountModule ? $accountModule->getService('PrestaShop\Module\PsAccounts\Service\OAuth2\Token\Validator\Validator') : null;

            if (!$accountModule || $shopContext === null || $validatorService === null) {
                return null;
            }

            $mcpScopes = ['mcp.discover', 'mcp.read', 'mcp.write', 'email'];
            $mcpUrl = rtrim((string) $shopContext->getBaseURL(true), '/') . '/mcp';

            $tokenData = $validatorService->validateToken($jwt, $mcpScopes, [$mcpUrl]);
            $userData = $mcpAllowedUsersService->getUserByEmail($tokenData->email);

            return !empty($userData) ? ($userData['role'] ?? null) : null;
        } catch (\Exception $e) {
            $this->logger->info('OAuth validation failed, will try token fallback', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function tryTokenAuth(string $encodedValue, McpAllowedUsersService $mcpAllowedUsersService): ?string
    {
        $decoded = base64_decode($encodedValue, true);

        if ($decoded === false || !str_contains($decoded, ':')) {
            return null;
        }

        [$email, $token] = explode(':', $decoded, 2);

        $mcpUser = $mcpAllowedUsersService->getUserByEmail($email);

        if (empty($mcpUser) || empty($mcpUser['token_hash'])) {
            password_verify('dummy', '$2y$10$' . str_repeat('A', 53));

            return null;
        }

        return password_verify($token, $mcpUser['token_hash']) ? ($mcpUser['role'] ?? null) : null;
    }
}
