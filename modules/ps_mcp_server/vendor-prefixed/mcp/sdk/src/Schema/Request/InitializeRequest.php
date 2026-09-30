<?php

namespace PsMcpServerDeps\Mcp\Schema\Request;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\ClientCapabilities;
use PsMcpServerDeps\Mcp\Schema\Implementation;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;

final class InitializeRequest extends Request
{
    public function __construct(
        public readonly string $protocolVersion,
        public readonly ClientCapabilities $capabilities,
        public readonly Implementation $clientInfo,
    ) {
    }

    public static function getMethod(): string
    {
        return 'initialize';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['protocolVersion'])) {
            throw new InvalidArgumentException('protocolVersion is required');
        }

        if (!isset($params['capabilities'])) {
            throw new InvalidArgumentException('capabilities is required');
        }
        $capabilities = ClientCapabilities::fromArray($params['capabilities']);

        if (!isset($params['clientInfo'])) {
            throw new InvalidArgumentException('clientInfo is required');
        }
        $clientInfo = Implementation::fromArray($params['clientInfo']);

        return new self($params['protocolVersion'], $capabilities, $clientInfo);
    }

    protected function getParams(): array
    {
        return [
            'protocolVersion' => $this->protocolVersion,
            'capabilities' => $this->capabilities,
            'clientInfo' => $this->clientInfo,
        ];
    }
}
