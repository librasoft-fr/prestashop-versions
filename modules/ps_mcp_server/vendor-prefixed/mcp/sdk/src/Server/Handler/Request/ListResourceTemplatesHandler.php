<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\ListResourceTemplatesRequest;
use PsMcpServerDeps\Mcp\Schema\Result\ListResourceTemplatesResult;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class ListResourceTemplatesHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly int $pageSize = 20,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof ListResourceTemplatesRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response
    {
        \assert($request instanceof ListResourceTemplatesRequest);

        $page = $this->registry->getResourceTemplates($this->pageSize, $request->cursor);

        return new Response(
            $request->getId(),
            new ListResourceTemplatesResult($page->references, $page->nextCursor),
        );
    }
}
