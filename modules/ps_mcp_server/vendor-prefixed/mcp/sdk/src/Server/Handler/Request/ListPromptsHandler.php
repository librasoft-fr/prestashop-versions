<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\ListPromptsRequest;
use PsMcpServerDeps\Mcp\Schema\Result\ListPromptsResult;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class ListPromptsHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly int $pageSize = 20,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof ListPromptsRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response
    {
        \assert($request instanceof ListPromptsRequest);

        $page = $this->registry->getPrompts($this->pageSize, $request->cursor);

        return new Response(
            $request->getId(),
            new ListPromptsResult($page->references, $page->nextCursor),
        );
    }
}
