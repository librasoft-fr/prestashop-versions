<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Enum\ProtocolVersion;
use Mcp\Schema\Implementation;
use Mcp\Schema\JsonRpc\MessageInterface;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\JsonRpc\ResultInterface;
use Mcp\Schema\ServerCapabilities;


class InitializeResult implements ResultInterface
{
    
    public function __construct(
        public readonly ServerCapabilities $capabilities,
        public readonly Implementation $serverInfo,
        public readonly ?string $instructions = null,
        public readonly ?array $meta = null,
        public readonly ?ProtocolVersion $protocolVersion = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['protocolVersion']) || !\is_string($data['protocolVersion'])) {
            throw new InvalidArgumentException('Missing or invalid "protocolVersion".');
        }
        if (!isset($data['capabilities']) || !\is_array($data['capabilities'])) {
            throw new InvalidArgumentException('Missing or invalid "capabilities".');
        }
        if (!isset($data['serverInfo']) || !\is_array($data['serverInfo'])) {
            throw new InvalidArgumentException('Missing or invalid "serverInfo".');
        }

        return new self(
            ServerCapabilities::fromArray($data['capabilities']),
            Implementation::fromArray($data['serverInfo']),
            $data['instructions'] ?? null,
            $data['_meta'] ?? null,
            ProtocolVersion::tryFrom($data['protocolVersion']),
        );
    }

    
    public function jsonSerialize(): array
    {
        $protocolVersion = $this->protocolVersion ?? MessageInterface::PROTOCOL_VERSION;
        $data = [
            'protocolVersion' => $protocolVersion->value,
            'capabilities' => $this->capabilities,
            'serverInfo' => $this->serverInfo,
        ];
        if (null !== $this->instructions) {
            $data['instructions'] = $this->instructions;
        }
        if (null !== $this->meta) {
            $data['_meta'] = $this->meta;
        }

        return $data;
    }
}
