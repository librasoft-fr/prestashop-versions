<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\ListToolsRequest;
use PsMcpServerDeps\Mcp\Schema\Result\ListToolsResult;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class ListToolsHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly int $pageSize = 20,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof ListToolsRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response
    {
        \assert($request instanceof ListToolsRequest);

        $page = $this->registry->getTools($this->pageSize, $request->cursor);

        return new Response(
            $request->getId(),
            new ListToolsResult($page->references, $page->nextCursor),
        );
    }
}
