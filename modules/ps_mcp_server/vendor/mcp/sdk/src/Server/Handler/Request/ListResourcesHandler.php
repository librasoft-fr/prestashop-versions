<?php



namespace Mcp\Server\Handler\Request;

use Mcp\Capability\RegistryInterface;
use Mcp\Exception\InvalidCursorException;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\Request\ListResourcesRequest;
use Mcp\Schema\Result\ListResourcesResult;
use Mcp\Server\Session\SessionInterface;


final class ListResourcesHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly int $pageSize = 20,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof ListResourcesRequest;
    }

    
    public function handle(Request $request, SessionInterface $session): Response
    {
        \assert($request instanceof ListResourcesRequest);

        $page = $this->registry->getResources($this->pageSize, $request->cursor);

        return new Response(
            $request->getId(),
            new ListResourcesResult($page->references, $page->nextCursor),
        );
    }
}
