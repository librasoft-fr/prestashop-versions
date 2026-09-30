<?php



namespace Mcp;

use Mcp\Client\Builder;
use Mcp\Client\Configuration;
use Mcp\Client\Protocol;
use Mcp\Client\Transport\TransportInterface;
use Mcp\Exception\ConnectionException;
use Mcp\Exception\RequestException;
use Mcp\Schema\Enum\LoggingLevel;
use Mcp\Schema\Implementation;
use Mcp\Schema\JsonRpc\Error;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\PromptReference;
use Mcp\Schema\Request\CallToolRequest;
use Mcp\Schema\Request\CompletionCompleteRequest;
use Mcp\Schema\Request\GetPromptRequest;
use Mcp\Schema\Request\ListPromptsRequest;
use Mcp\Schema\Request\ListResourcesRequest;
use Mcp\Schema\Request\ListResourceTemplatesRequest;
use Mcp\Schema\Request\ListToolsRequest;
use Mcp\Schema\Request\PingRequest;
use Mcp\Schema\Request\ReadResourceRequest;
use Mcp\Schema\Request\SetLogLevelRequest;
use Mcp\Schema\ResourceReference;
use Mcp\Schema\Result\CallToolResult;
use Mcp\Schema\Result\CompletionCompleteResult;
use Mcp\Schema\Result\GetPromptResult;
use Mcp\Schema\Result\ListPromptsResult;
use Mcp\Schema\Result\ListResourcesResult;
use Mcp\Schema\Result\ListResourceTemplatesResult;
use Mcp\Schema\Result\ListToolsResult;
use Mcp\Schema\Result\ReadResourceResult;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class Client
{
    private ?TransportInterface $transport = null;

    public function __construct(
        private readonly Protocol $protocol,
        private readonly Configuration $config,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    
    public static function builder(): Builder
    {
        return new Builder();
    }

    
    public function connect(TransportInterface $transport): void
    {
        $this->transport = $transport;
        $this->protocol->connect($transport, $this->config);

        $transport->connect();

        $this->logger->info('Client connected and initialized');
    }

    
    public function isConnected(): bool
    {
        return null !== $this->transport && $this->protocol->getState()->isInitialized();
    }

    
    public function getServerInfo(): ?Implementation
    {
        return $this->protocol->getState()->getServerInfo();
    }

    
    public function getInstructions(): ?string
    {
        return $this->protocol->getState()->getInstructions();
    }

    
    public function ping(): void
    {
        $request = new PingRequest();

        $this->sendRequest($request);
    }

    
    public function listTools(?string $cursor = null): ListToolsResult
    {
        $request = new ListToolsRequest($cursor);

        $response = $this->sendRequest($request);

        return ListToolsResult::fromArray($response->result);
    }

    
    public function callTool(string $name, array $arguments = [], ?callable $onProgress = null): CallToolResult
    {
        $request = new CallToolRequest($name, $arguments);

        $response = $this->sendRequest($request, $onProgress);

        return CallToolResult::fromArray($response->result);
    }

    
    public function listResources(?string $cursor = null): ListResourcesResult
    {
        $request = new ListResourcesRequest($cursor);

        $response = $this->sendRequest($request);

        return ListResourcesResult::fromArray($response->result);
    }

    
    public function listResourceTemplates(?string $cursor = null): ListResourceTemplatesResult
    {
        $request = new ListResourceTemplatesRequest($cursor);

        $response = $this->sendRequest($request);

        return ListResourceTemplatesResult::fromArray($response->result);
    }

    
    public function readResource(string $uri, ?callable $onProgress = null): ReadResourceResult
    {
        $request = new ReadResourceRequest($uri);

        $response = $this->sendRequest($request, $onProgress);

        return ReadResourceResult::fromArray($response->result);
    }

    
    public function listPrompts(?string $cursor = null): ListPromptsResult
    {
        $request = new ListPromptsRequest($cursor);

        $response = $this->sendRequest($request);

        return ListPromptsResult::fromArray($response->result);
    }

    
    public function getPrompt(string $name, array $arguments = [], ?callable $onProgress = null): GetPromptResult
    {
        $request = new GetPromptRequest($name, $arguments);

        $response = $this->sendRequest($request, $onProgress);

        return GetPromptResult::fromArray($response->result);
    }

    
    public function complete(PromptReference|ResourceReference $ref, array $argument): CompletionCompleteResult
    {
        $request = new CompletionCompleteRequest($ref, $argument);

        $response = $this->sendRequest($request);

        return CompletionCompleteResult::fromArray($response->result);
    }

    
    public function setLoggingLevel(LoggingLevel $level): void
    {
        $request = new SetLogLevelRequest($level);

        $this->sendRequest($request);
    }

    
    private function sendRequest(Request $request, ?callable $onProgress = null): Response
    {
        if (!$this->isConnected()) {
            throw new ConnectionException('Client is not connected. Call connect() first.');
        }

        $withProgress = null !== $onProgress;
        $fiber = new \Fiber(fn () => $this->protocol->request($request, $this->config->requestTimeout, $withProgress));
        $response = $this->transport->runRequest($fiber, $onProgress);

        if ($response instanceof Error) {
            throw RequestException::fromError($response);
        }

        return $response;
    }

    
    public function disconnect(): void
    {
        if (null !== $this->transport) {
            $this->transport->close();
            $this->transport = null;
            $this->logger->info('Client disconnected');
        }
    }
}
