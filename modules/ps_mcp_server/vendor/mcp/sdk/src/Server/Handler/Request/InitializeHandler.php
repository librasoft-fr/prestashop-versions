<?php



namespace Mcp\Server\Handler\Request;

use Mcp\Schema\Implementation;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\Request\InitializeRequest;
use Mcp\Schema\Result\InitializeResult;
use Mcp\Schema\ServerCapabilities;
use Mcp\Server\Configuration;
use Mcp\Server\Session\SessionInterface;


final class InitializeHandler implements RequestHandlerInterface
{
    public function __construct(
        public readonly ?Configuration $configuration = null,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof InitializeRequest;
    }

    
    public function handle(Request $request, SessionInterface $session): Response
    {
        \assert($request instanceof InitializeRequest);

        $session->set('client_info', $request->clientInfo->jsonSerialize());
        $session->set('client_capabilities', $request->capabilities->jsonSerialize());

        return new Response(
            $request->getId(),
            new InitializeResult(
                $this->configuration->capabilities ?? new ServerCapabilities(),
                $this->configuration->serverInfo ?? new Implementation(),
                $this->configuration?->instructions,
                null,
                $this->configuration?->protocolVersion,
            ),
        );
    }
}
