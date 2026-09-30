<?php

namespace PsMcpServerDeps\Mcp\Schema\JsonRpc;

use PsMcpServerDeps\Mcp\Schema\Enum\ProtocolVersion;

interface MessageInterface extends \JsonSerializable
{
    public const JSONRPC_VERSION = '2.0';
    public const PROTOCOL_VERSION = ProtocolVersion::V2025_06_18;
}
