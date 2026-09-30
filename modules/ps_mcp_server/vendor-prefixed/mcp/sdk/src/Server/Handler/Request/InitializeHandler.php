<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Schema\Implementation;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\InitializeRequest;
use PsMcpServerDeps\Mcp\Schema\Result\InitializeResult;
use PsMcpServerDeps\Mcp\Schema\ServerCapabilities;
use PsMcpServerDeps\Mcp\Server\Configuration;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

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
