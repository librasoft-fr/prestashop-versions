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

namespace PrestaShop\Module\PsMcpServer\Tracker;

use PrestaShop\Module\PsMcpServer\Helper\ShopIdHelper;
use PrestaShop\Module\PsMcpServer\Http\SegmentClient;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Segment
{
    private ?string $apiKey;
    private \Context $context;
    private ?SegmentClient $segmentClient = null;

    public function __construct(\Context $context)
    {
        $this->context = $context;
        $config = \Ps_mcp_server::getConfig();

        $this->apiKey = $config['SEGMENT_API_KEY'] ?? null;

        if (isset($this->apiKey) && !empty($this->apiKey)) {
            $this->segmentClient = new SegmentClient($this->apiKey, null);
        }
    }

    public function trackMessage(string $message, ?array $options = null): bool
    {
        if (empty($message)) {
            throw new \PrestaShopException('Message cannot be empty.');
        }

        if (!isset($this->segmentClient)) {
            return false;
        }

        $locale = $this->context->language->locale ?? 'unknown';
        $rawUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userAgent = substr(
            filter_var($rawUserAgent, FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            0,
            256
        );

        $shopId = ShopIdHelper::getShopId();

        $data = [
            'event' => $message,
            'context' => [
                'ps_version' => _PS_VERSION_,
                'userAgent' => $userAgent,
                'locale' => $locale,
                'shop_id' => $shopId,
            ],
            'properties' => $options,
        ];

        if (isset($shopId) && !empty($shopId)) {
            $data['userId'] = $shopId;
        } else {
            $data['anonymousId'] = $this->getOrCreateAnonTrackingId();
        }
        $request = $this->segmentClient->track($data);

        return $request['success'] ?? false;
    }

    private function getOrCreateAnonTrackingId(): string
    {
        $anonId = \Configuration::get('PS_MCP_SERVER_ANON_TRACKING');

        if (empty($anonId)) {
            $anonId = bin2hex(random_bytes(16));
            \Configuration::updateValue('PS_MCP_SERVER_ANON_TRACKING', $anonId);
        }

        return $anonId;
    }
}
