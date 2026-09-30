<?php

namespace PsMcpServerDeps\Mcp\Client;

use PsMcpServerDeps\Mcp\Schema\ClientCapabilities;
use PsMcpServerDeps\Mcp\Schema\Enum\ProtocolVersion;
use PsMcpServerDeps\Mcp\Schema\Implementation;

class Configuration
{
    public function __construct(
        public readonly Implementation $clientInfo,
        public readonly ClientCapabilities $capabilities,
        public readonly ProtocolVersion $protocolVersion = ProtocolVersion::V2025_06_18,
        public readonly int $initTimeout = 30,
        public readonly int $requestTimeout = 120,
        public readonly int $maxRetries = 3,
    ) {
    }
}
