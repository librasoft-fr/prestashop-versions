<?php



namespace Mcp\Server;

use Mcp\Schema\Enum\ProtocolVersion;
use Mcp\Schema\Implementation;
use Mcp\Schema\ServerCapabilities;


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
