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

namespace PrestaShop\Module\PsMcpServer\Http;

use PrestaShop\Module\PsMcpServer\Tracker\Segment;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CloudSyncClient
{
    private Client $httpClient;
    private ?string $shopId = null;
    private ?string $bearerToken = null;
    private Segment $segment;

    public function __construct(?Client $httpClient = null)
    {
        $context = \Context::getContext();

        if ($context === null) {
            throw new \PrestaShopException('Context is not defined');
        }

        $this->httpClient = $httpClient ?? Client::getInstance();
        $this->segment = new Segment($context);
        $this->initializeCredentials();
    }

    private function initializeCredentials(): void
    {
        $accountsModule = \Module::getInstanceByName('ps_accounts');

        if (!$accountsModule) {
            return;
        }

        $accountsService = $accountsModule->getService('PrestaShop\Module\PsAccounts\Service\PsAccountsService');

        if ($accountsService) {
            $this->shopId = $accountsService->getShopUuid();
            $this->bearerToken = $accountsService->getOrRefreshToken();
        }
    }

    public function hasCredentials(): bool
    {
        return !empty($this->shopId) && !empty($this->bearerToken);
    }

    private function buildConsentData(): array
    {
        return [
            'moduleName' => 'ps_mcp_server',
            'shopConsentAccepted' => ['info', 'modules', 'themes', 'orders', 'carriers'],
            'shopConsentRevoked' => [],
        ];
    }

    public function consentExists(): bool
    {
        if ((bool) \Configuration::get('PS_MCP_SERVER_CLOUDSYNC_ALREADY_ONBOARDED')) {
            return true;
        }

        if (!$this->hasCredentials()) {
            return false;
        }

        $headers = [
            HttpConstants::HEADER_ACCEPT => HttpConstants::CONTENT_TYPE_JSON,
            HttpConstants::HEADER_AUTHORIZATION => 'Bearer ' . $this->bearerToken,
        ];

        $url = \Ps_mcp_server::getConfig()['CLOUDSYNC_API_URL'] . 'consent/' . $this->shopId . '?moduleName=ps_mcp_server';

        $request = $this->httpClient->get($url, $headers);

        $response = $request->getResponse();
        $responseData = $response ? json_decode($response, true) : null;

        return $responseData
            && isset($responseData['consents'])
            && !empty($responseData['consents']);
    }

    public function sendConsent(): array
    {
        if (!$this->hasCredentials()) {
            return [
                'status' => false,
                'httpCode' => 500,
                'body' => null,
                'error' => 'Missing credentials',
            ];
        }

        $consentData = $this->buildConsentData();

        $headers = [
            HttpConstants::HEADER_ACCEPT => HttpConstants::CONTENT_TYPE_JSON,
            HttpConstants::HEADER_CONTENT_TYPE => HttpConstants::CONTENT_TYPE_JSON,
            HttpConstants::HEADER_AUTHORIZATION => 'Bearer ' . $this->bearerToken,
        ];

        $url = \Ps_mcp_server::getConfig()['CLOUDSYNC_API_URL'] . 'consent/' . $this->shopId . '?moduleName=ps_mcp_server';

        $request = $this->httpClient->post($url, $headers, $consentData);

        if ($request->getHttpStatus() === 201) {
            $this->segment->trackMessage('CloudSync Onboarding Completed', ['module_name' => 'ps_mcp_server']);
        }

        return [
            'httpCode' => $request->getHttpStatus(),
            'body' => $request->getResponse(),
        ];
    }
}
