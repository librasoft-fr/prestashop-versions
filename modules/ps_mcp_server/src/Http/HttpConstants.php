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

class HttpConstants
{
    public const CONTENT_TYPE_JSON = 'application/json';

    public const CONTENT_TYPE_SSE = 'text/event-stream';

    public const HEADER_CONTENT_TYPE = 'Content-Type';

    public const HEADER_ACCEPT = 'Accept';

    public const HEADER_AUTHORIZATION = 'Authorization';

    public const JSON_CONTENT_TYPE_HEADER = 'Content-Type: application/json';

    public const SSE_CONTENT_TYPE_HEADER = 'Content-Type: text/event-stream';
}
