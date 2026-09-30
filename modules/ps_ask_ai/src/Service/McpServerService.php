<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class McpServerService
{
    public const MCP_USER_EMAIL = 'ps-ask-ai@prestashop.com';

    private const MCP_SERVER_MODULE_NAME = 'ps_mcp_server';

    public function __construct(private readonly \Context $context)
    {
    }

    public function getShopBaseUrl(): string
    {
        return rtrim((string) $this->context->shop->getBaseURL(true), '/');
    }

    public function getMcpServerUrl(): ?string
    {
        if (!$this->isMcpServerAvailable()) {
            return null;
        }

        $baseUrl = $this->context->shop->getBaseURL(true);

        return rtrim((string) $baseUrl, '/') . '/mcp/';
    }

    /**
     * Fetch a raw MCP bearer token for the given user.
     * Calling this regenerates the user's token, invalidating any previously issued one.
     */
    public function getMcpToken(string $email = self::MCP_USER_EMAIL, string $role = 'editor'): ?string
    {
        if (!$this->isMcpServerAvailable()) {
            return null;
        }

        $container = SymfonyContainer::getInstance();
        if (null === $container || !$container->has('PrestaShop\Module\PsMcpServer\Services\McpAllowedUsersService')) {
            return null;
        }

        $service = $container->get('PrestaShop\Module\PsMcpServer\Services\McpAllowedUsersService');

        $result = $service->getUserByEmail($email)
            ? $service->regenerateTokenForUser($email)
            : $service->addAllowedUser($email, $role);

        return is_array($result) ? ($result['token'] ?? null) : null;
    }

    private function isMcpServerAvailable(): bool
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        if ($moduleManagerBuilder == null) {
            throw new \Exception('ModuleManagerBuilder is not defined');
        }

        $moduleManager = $moduleManagerBuilder->build();

        return $moduleManager->isInstalled((string) self::MCP_SERVER_MODULE_NAME) && $moduleManager->isEnabled((string) self::MCP_SERVER_MODULE_NAME);
    }
}
