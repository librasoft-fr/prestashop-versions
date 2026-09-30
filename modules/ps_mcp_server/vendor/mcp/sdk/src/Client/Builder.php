<?php



namespace Mcp\Client;

use Mcp\Client;
use Mcp\Client\Handler\Notification\NotificationHandlerInterface;
use Mcp\Client\Handler\Request\RequestHandlerInterface;
use Mcp\Schema\ClientCapabilities;
use Mcp\Schema\Enum\ProtocolVersion;
use Mcp\Schema\Implementation;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


final class Builder
{
    private string $name = 'mcp-php-client';
    private string $version = '1.0.0';
    private ?string $description = null;
    private ?ProtocolVersion $protocolVersion = null;
    private ?ClientCapabilities $capabilities = null;
    private int $initTimeout = 30;
    private int $requestTimeout = 120;
    private int $maxRetries = 3;
    private ?LoggerInterface $logger = null;

    
    private array $notificationHandlers = [];

    
    private array $requestHandlers = [];

    
    public function setClientInfo(string $name, string $version, ?string $description = null): self
    {
        $this->name = $name;
        $this->version = $version;
        $this->description = $description;

        return $this;
    }

    
    public function setProtocolVersion(ProtocolVersion $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    
    public function setCapabilities(ClientCapabilities $capabilities): self
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    
    public function setInitTimeout(int $seconds): self
    {
        $this->initTimeout = $seconds;

        return $this;
    }

    
    public function setRequestTimeout(int $seconds): self
    {
        $this->requestTimeout = $seconds;

        return $this;
    }

    
    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = $retries;

        return $this;
    }

    
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    
    public function addNotificationHandler(NotificationHandlerInterface $handler): self
    {
        $this->notificationHandlers[] = $handler;

        return $this;
    }

    
    public function addRequestHandler(RequestHandlerInterface $handler): self
    {
        $this->requestHandlers[] = $handler;

        return $this;
    }

    
    public function build(): Client
    {
        $logger = $this->logger ?? new NullLogger();

        $clientInfo = new Implementation(
            $this->name,
            $this->version,
            $this->description,
        );

        $config = new Configuration(
            clientInfo: $clientInfo,
            capabilities: $this->capabilities ?? new ClientCapabilities(),
            protocolVersion: $this->protocolVersion ?? ProtocolVersion::V2025_06_18,
            initTimeout: $this->initTimeout,
            requestTimeout: $this->requestTimeout,
            maxRetries: $this->maxRetries,
        );

        $protocol = new Protocol(
            requestHandlers: $this->requestHandlers,
            notificationHandlers: $this->notificationHandlers,
            logger: $logger,
        );

        return new Client($protocol, $config, $logger);
    }
}
