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

if (!defined('_PS_VERSION_')) {
    exit;
}

const SEGMENT_URL = 'https://api.segment.io/v1/';

class SegmentClient
{
    private Client $httpClient;
    private string $writeKey;
    private static string $trackEndpoint = SEGMENT_URL . 'track';

    public function __construct(string $writeKey, ?Client $httpClient)
    {
        $this->httpClient = $httpClient ?? Client::getInstance();
        $this->writeKey = $writeKey;
    }

    public function track(array $data): array
    {
        $headers = [
            HttpConstants::HEADER_ACCEPT => HttpConstants::CONTENT_TYPE_JSON,
            HttpConstants::HEADER_CONTENT_TYPE => HttpConstants::CONTENT_TYPE_JSON,
        ];
        $data['writeKey'] = $this->writeKey;

        $request = $this->httpClient->post(self::$trackEndpoint, $headers, $data);

        return [
            'success' => substr((string) $request->getHttpStatus(), 0, 1) === '2',
            'httpCode' => $request->getHttpStatus(),
            'body' => $request->getResponse(),
        ];
    }
}
