<?php



namespace Mcp\Server\Handler\Request;

use Mcp\Capability\RegistryInterface;
use Mcp\Exception\InvalidCursorException;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\Request\ListResourceTemplatesRequest;
use Mcp\Schema\Result\ListResourceTemplatesResult;
use Mcp\Server\Session\SessionInterface;


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
