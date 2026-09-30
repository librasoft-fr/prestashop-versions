<?php

namespace PsMcpServerDeps\Mcp\Server;

use PsMcpServerDeps\Mcp\Schema\Enum\ProtocolVersion;
use PsMcpServerDeps\Mcp\Schema\Implementation;
use PsMcpServerDeps\Mcp\Schema\ServerCapabilities;

class Configuration
{
    public function __construct(
        public readonly Implementation $serverInfo,
        public readonly ServerCapabilities $capabilities,
        public readonly int $paginationLimit = 50,
        public readonly ?string $instructions = null,
        public readonly ?ProtocolVersion $protocolVersion = null,
    ) {
    }
}
